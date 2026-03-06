<x-guest-layout>
    <div class="flex flex-col items-center justify-center w-full mt-8">
        
        <div class="flex flex-row items-center justify-center gap-8 mb-10 w-full max-w-md">
            <img src="{{ asset('images/aromas_logo_blanco.png') }}" alt="Logo Aromas" class="h-24 w-auto object-contain drop-shadow-lg" />
            <div class="h-16 border-l border-aromas-tertiary"></div>
            <img src="{{ asset('images/br_logo_dorado_transparente-norelleno.png') }}" alt="Logo T.E.R.A." class="h-24 w-auto object-contain drop-shadow-lg" /> 
        </div>

        <div class="w-full sm:max-w-md px-8 py-10 bg-aromas-secondary/80 backdrop-blur-md border border-aromas-tertiary shadow-2xl sm:rounded-2xl">
            
            <div class="mb-6 text-sm text-gray-300 leading-relaxed text-center">
                ¡Gracias por registrarte! Antes de comenzar, ¿podrías verificar tu dirección de correo electrónico haciendo clic en el enlace que te acabamos de enviar? Si no recibiste el correo, con gusto te enviaremos otro.
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 font-medium text-sm text-aromas-success text-center bg-green-900/20 border border-green-500/30 p-3 rounded-lg">
                    Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionaste durante el registro.
                </div>
            @endif

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-6">
                <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="w-full flex justify-center items-center px-4 py-3 bg-aromas-highlight border border-transparent rounded-xl font-bold text-aromas-main uppercase tracking-widest hover:bg-yellow-400 active:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-aromas-highlight focus:ring-offset-2 focus:ring-offset-aromas-secondary transition ease-in-out duration-150 shadow-lg text-xs">
                        Reenviar correo
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto text-center">
                    @csrf
                    <button type="submit" class="text-sm text-aromas-highlight hover:text-yellow-400 font-bold transition-colors focus:outline-none focus:underline uppercase tracking-widest">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>