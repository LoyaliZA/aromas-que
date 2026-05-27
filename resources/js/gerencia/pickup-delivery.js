function cropSignatureCanvas(canvas) {
    const ctx = canvas.getContext('2d');
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imageData.data;

    let minX = canvas.width;
    let minY = canvas.height;
    let maxX = 0;
    let maxY = 0;
    let hasContent = false;

    for (let y = 0; y < canvas.height; y++) {
        for (let x = 0; x < canvas.width; x++) {
            const alpha = data[(y * canvas.width + x) * 4 + 3];
            if (alpha > 0) {
                minX = Math.min(minX, x);
                minY = Math.min(minY, y);
                maxX = Math.max(maxX, x);
                maxY = Math.max(maxY, y);
                hasContent = true;
            }
        }
    }

    if (!hasContent) return canvas.toDataURL();

    const padding = 20;
    minX = Math.max(0, minX - padding);
    minY = Math.max(0, minY - padding);
    maxX = Math.min(canvas.width, maxX + padding);
    maxY = Math.min(canvas.height, maxY + padding);

    const croppedCanvas = document.createElement('canvas');
    croppedCanvas.width = maxX - minX + padding * 2;
    croppedCanvas.height = maxY - minY + padding * 2;
    const croppedCtx = croppedCanvas.getContext('2d');
    croppedCtx.fillStyle = '#ffffff';
    croppedCtx.fillRect(0, 0, croppedCanvas.width, croppedCanvas.height);
    croppedCtx.drawImage(
        canvas,
        minX,
        minY,
        maxX - minX,
        maxY - minY,
        padding,
        padding,
        maxX - minX,
        maxY - minY
    );

    return croppedCanvas.toDataURL('image/webp', 0.7);
}

async function compressDeliveryImage(file, maxWidth = 1280, maxHeight = 1280, quality = 0.7) {
    if (!file.type.startsWith('image/')) return file;
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > maxWidth) {
                        height = Math.round((height *= maxWidth / width));
                        width = maxWidth;
                    }
                } else if (height > maxHeight) {
                    width = Math.round((width *= maxHeight / height));
                    height = maxHeight;
                }

                canvas.width = width;
                canvas.height = height;
                canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                canvas.toBlob(
                    (blob) => {
                        resolve(
                            new File([blob], file.name.replace(/\.[^/.]+$/, '') + '.webp', {
                                type: 'image/webp',
                                lastModified: Date.now(),
                            })
                        );
                    },
                    'image/webp',
                    quality
                );
            };
        };
    });
}

document.addEventListener('alpine:init', () => {
    if (!Alpine.store('firmaStore')) {
        Alpine.store('firmaStore', { isFullScreen: false, preview: null });
    }

    Alpine.data('gerenciaPickupDelivery', () => ({
        showDeliveryModal: false,
        pickup: {},
        isThirdParty: false,
        receiverName: '',
        signaturePad: null,
        signatureData: '',
        isPadEmpty: true,
        evidencePreview: null,
        clientSearchQuery: '',
        clientSearchResults: [],
        showClientDropdown: false,
        selectedCustomerId: '',
        redirectTo: window.__gerenciaDeliveryConfig?.redirectTo ?? '/gerencia/daily',

        openModal(data) {
            if (!window.dailyAlertsBridge) window.dailyAlertsBridge = {};
            window.dailyAlertsBridge.deliveryModalOpen = true;

            this.pickup = data || {};
            this.isThirdParty = !!data?.is_third_party;
            this.receiverName = data?.is_third_party ? (data.receiver_name || '') : (data?.client_name || '');
            this.clientSearchQuery = this.receiverName;
            this.selectedCustomerId = data?.client_ref_id ? String(data.client_ref_id) : '';
            this.evidencePreview = null;
            this.signatureData = '';
            this.isPadEmpty = true;
            Alpine.store('firmaStore').preview = null;
            this.showDeliveryModal = true;
            setTimeout(() => this.initPad(), 150);
        },

        closeModal() {
            if (window.dailyAlertsBridge) {
                window.dailyAlertsBridge.deliveryModalOpen = false;
            }
            this.showDeliveryModal = false;
            this.evidencePreview = null;
            const fileInput = document.getElementById('gerencia_evidence_file');
            if (fileInput) fileInput.value = '';
            Alpine.store('firmaStore').isFullScreen = false;
        },

        searchCustomers() {
            this.clientSearchQuery = this.receiverName || this.clientSearchQuery || '';
            if (this.isThirdParty || this.clientSearchQuery.length < 2) {
                this.clientSearchResults = [];
                this.showClientDropdown = false;
                return;
            }
            fetch(
                `/gerencia/customers/search?q=${encodeURIComponent(this.clientSearchQuery)}`,
                { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } }
            )
                .then((r) => r.json())
                .then((data) => {
                    this.clientSearchResults = data;
                    this.showClientDropdown = data.length > 0;
                });
        },

        selectCustomer(customer) {
            this.selectedCustomerId = String(customer.id);
            this.clientSearchQuery = customer.name;
            this.receiverName = customer.name;
            this.showClientDropdown = false;
        },

        async handleEvidenceChange(event) {
            const file = event.target.files[0];
            if (!file) {
                this.evidencePreview = null;
                return;
            }
            const compressed = await compressDeliveryImage(file);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(compressed);
            event.target.files = dataTransfer.files;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.evidencePreview = e.target.result;
            };
            reader.readAsDataURL(compressed);
        },

        removeEvidence() {
            this.evidencePreview = null;
            const fileInput = document.getElementById('gerencia_evidence_file');
            if (fileInput) fileInput.value = '';
        },

        initPad() {
            const canvas = this.$refs.signature_canvas;
            if (!canvas || typeof SignaturePad === 'undefined') return;
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            if (this.signaturePad) {
                this.signaturePad.clear();
            } else {
                this.signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255,255,255,0)',
                    penColor: 'rgb(0,0,0)',
                    velocityFilterWeight: 0.7,
                });
                this.signaturePad.addEventListener('beginStroke', () => {
                    this.isPadEmpty = false;
                });
            }
            this.isPadEmpty = true;
        },

        clearPad() {
            if (this.signaturePad) {
                this.signaturePad.clear();
                this.isPadEmpty = true;
                this.signatureData = '';
                Alpine.store('firmaStore').preview = null;
            }
        },

        confirmSignature() {
            if (!this.signaturePad || this.signaturePad.isEmpty()) {
                alert('Por favor, firme el documento antes de confirmar.');
                return;
            }
            const cropped = cropSignatureCanvas(this.$refs.signature_canvas);
            Alpine.store('firmaStore').preview = cropped;
            this.signatureData = cropped;
            Alpine.store('firmaStore').isFullScreen = false;
        },

        submitDelivery() {
            if (!this.signatureData && (!this.signaturePad || this.signaturePad.isEmpty())) {
                alert('La firma es obligatoria.');
                return;
            }
            if (!this.evidencePreview) {
                alert('La foto de evidencia es obligatoria.');
                return;
            }
            if (this.isThirdParty && !this.receiverName.trim()) {
                alert('Indica el nombre de quien recibe el paquete.');
                return;
            }
            if (!this.isThirdParty && !this.clientSearchQuery.trim()) {
                alert('Confirma o busca al titular en la base de clientes.');
                return;
            }

            if (!this.signatureData && this.signaturePad) {
                this.signatureData = cropSignatureCanvas(this.$refs.signature_canvas);
            }

            this.$nextTick(() => {
                document.getElementById('gerenciaDeliveryForm').submit();
            });
        },
    }));
});
