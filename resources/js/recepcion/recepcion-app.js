window.deliveryApp = function(config) {
    return {
        search: '', isLoading: false, queueCount: config.queueCount,
        showDeliveryModal: false, showQueueModal: false, showQueueListModal: false, showAbandonModal: false, 
        pickup: {}, isThirdParty: false, receiverName: '', signaturePad: null, signatureData: '', isPadEmpty: true,
        queueType: 'SALES', waitingClients: [], evidencePreview: null, evidenceName: '', 
        clientSearchQuery: '', clientSearchResults: [], showClientDropdown: false, selectedCustomerId: '',
        selectedCustomerObj: null, abandoningClient: null, abandonReasonId: '', customAbandonReason: '',
        isThirdPartyQueue: false, representativeNameQueue: '', hasDisabilityQueue: false, isNewCustomerQueue: false, newClientName: '',

        validateQueueForm(e) {
            if (!this.isNewCustomerQueue && !this.selectedCustomerId) {
                e.preventDefault(); alert('Debe buscar y seleccionar un cliente.'); return false;
            }
            if (this.isNewCustomerQueue && this.newClientName.trim() === '') {
                e.preventDefault(); alert('Ingrese el nombre del cliente nuevo.'); return false;
            }
            if (this.isThirdPartyQueue && this.representativeNameQueue.trim() === '') {
                e.preventDefault(); alert('Ingrese el nombre del representante.'); return false;
            }
            return true;
        },

        init() {
            this.$watch('search', (value) => this.fetchData(value));
            this.$watch('clientSearchQuery', (value) => {
                if(this.selectedCustomerId && (!this.selectedCustomerObj || this.selectedCustomerObj.name !== value)) this.clearSelectedCustomer();
            });
            setInterval(() => {
                if (this.showDeliveryModal || this.showQueueModal || this.showQueueListModal || this.showAbandonModal || this.search.length > 0) return;
                this.fetchData('');
            }, 5000);
            window.addEventListener('open-delivery-modal', event => this.openDeliveryModal(event.detail));
        },

        fetchData(searchValue) {
            this.isLoading = true;
            let dept = document.getElementById('deptFilter') ? document.getElementById('deptFilter').value : 'ALL';
            let status = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : 'IN_CUSTODY';
            
            fetch(`${config.routes.dashboard}?search=${searchValue}&department=${dept}&status=${status}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('results-container').innerHTML = data.html;
                    this.queueCount = data.queueCount;
                    this.isLoading = false;
                }).catch(err => console.error(err));
        },

        openQueueModal() { this.showQueueModal = true; this.queueType = 'SALES'; this.clearSelectedCustomer(); this.clientSearchQuery = ''; },
        closeQueueModal() { this.showQueueModal = false; this.showClientDropdown = false; },

        searchCustomers() {
            if (this.clientSearchQuery.length < 2) { this.clientSearchResults = []; this.showClientDropdown = false; return; }
            fetch(`/recepcion/customers/search?q=${encodeURIComponent(this.clientSearchQuery)}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(r => r.json()).then(data => { this.clientSearchResults = data; this.showClientDropdown = data.length > 0; });
        },

        selectCustomer(customer) {
            this.selectedCustomerObj = customer; this.selectedCustomerId = customer.id; this.clientSearchQuery = customer.name;
            this.showClientDropdown = false; this.isThirdPartyQueue = false; this.representativeNameQueue = ''; this.hasDisabilityQueue = false;
        },
        
        clearSelectedCustomer() {
            this.selectedCustomerObj = null; this.selectedCustomerId = ''; this.isThirdPartyQueue = false;
            this.representativeNameQueue = ''; this.hasDisabilityQueue = false;
        },

        openQueueListModal() { this.fetchQueueList(); this.showQueueListModal = true; },

        fetchQueueList() {
            fetch(config.routes.queueList, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(r => r.json()).then(data => { if (data.clients) { this.waitingClients = data.clients; this.queueCount = data.clients.length; }});
        },

        openAbandonModal(client) { this.abandoningClient = client; this.abandonReasonId = ''; this.customAbandonReason = ''; this.showAbandonModal = true; },

        confirmAbandon() {
            if (!this.abandonReasonId) { alert("Selecciona un motivo."); return; }
            if (this.abandonReasonId == '4' && !this.customAbandonReason.trim()) { alert("Escribe el motivo."); return; }

            fetch(`/recepcion/queue/${this.abandoningClient.id}/abandon`, {
                method: 'PUT',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify({ abandonment_reason_id: this.abandonReasonId, custom_abandonment_reason: this.abandonReasonId == '4' ? this.customAbandonReason : null })
            }).then(r => r.json()).then(data => {
                if (data.success) { this.showAbandonModal = false; this.fetchQueueList(); this.fetchData(this.search); } 
                else { alert(data.message || 'Error'); }
            });
        },

        confirmReceipt(id) {
            fetch(`/recepcion/receive/${id}`, {
                method: 'PUT',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            }).then(r => r.json()).then(data => {
                if (data.success) { this.fetchData(this.search); } else { alert(data.message); }
            });
        },

        openDeliveryModal(data) {
            this.pickup = data; this.isThirdParty = !!data.is_third_party; this.receiverName = data.is_third_party ? data.receiver_name : '';
            this.showDeliveryModal = true; setTimeout(() => { this.initPad(); }, 100);
        },

        closeModal() {
            this.showDeliveryModal = false; this.evidencePreview = null; this.evidenceName = '';
            const fileInput = document.getElementById('evidence_file'); if (fileInput) fileInput.value = '';
        },

        handleEvidenceChange(event) {
            const file = event.target.files[0];
            if (!file) { this.evidencePreview = null; this.evidenceName = ''; return; }
            this.evidenceName = file.name; const reader = new FileReader();
            reader.onload = (e) => { this.evidencePreview = e.target.result; }; reader.readAsDataURL(file);
        },

        removeEvidence() { this.evidencePreview = null; this.evidenceName = ''; const fileInput = document.getElementById('evidence_file'); if (fileInput) fileInput.value = ''; },

        initPad() {
            const canvas = this.$refs.signature_canvas; if (!canvas) return;
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio; canvas.height = canvas.offsetHeight * ratio; canvas.getContext("2d").scale(ratio, ratio);
            if (this.signaturePad) { this.signaturePad.clear(); } 
            else { this.signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgba(255,255,255,0)', penColor: 'rgb(0,0,0)', velocityFilterWeight: 0.7 }); this.signaturePad.addEventListener("beginStroke", () => { this.isPadEmpty = false; }); }
            this.isPadEmpty = true;
        },

        clearPad() { if (this.signaturePad) { this.signaturePad.clear(); this.isPadEmpty = true; this.signatureData = ''; } },

        submitDelivery() {
            if (!this.signaturePad || this.signaturePad.isEmpty()) { alert('La firma es obligatoria.'); return; }
            if (!this.evidencePreview) { alert('La foto es obligatoria.'); return; }
            this.signatureData = this.signaturePad.toDataURL('image/png');
            this.$nextTick(() => { document.getElementById('deliveryForm').submit(); });
        }
    }
};