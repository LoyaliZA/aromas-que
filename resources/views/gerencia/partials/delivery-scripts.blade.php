@push('scripts')
<script>
    window.__gerenciaDeliveryConfig = {
        redirectTo: @json($deliveryRedirectTo ?? route('gerencia.daily')),
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
@vite(['resources/js/gerencia/pickup-delivery.js'])
@endpush
