<x-guest-layout>
    <div class="flex flex-col items-center justify-center w-full mt-8">
        
        <div class="flex flex-row items-center justify-center gap-8 mb-10 w-full max-w-md">
            <img src="{{ asset('images/aromas_logo_blanco.png') }}" alt="Logo Aromas" class="h-24 w-auto object-contain drop-shadow-lg" />
            <div class="h-16 border-l border-aromas-tertiary"></div>
            <img src="{{ asset('images/br_logo_dorado_transparente-norelleno.png') }}" alt="Logo T.E.R.A." class="h-24 w-auto object-contain drop-shadow-lg" /> 
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <div class="w-full sm:max-w-md px-8 py-10 bg-aromas-secondary/80 backdrop-blur-md border border-aromas-tertiary shadow-2xl sm:rounded-2xl">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <label for="email" class="block font-semibold text-gray-300 tracking-wide">
                        Correo Electrónico
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" 
                           class="block mt-2 w-full bg-aromas-main border-aromas-tertiary text-white focus:border-aromas-highlight focus:ring-aromas-highlight shadow-inner rounded-lg" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-aromas-error" />
                </div>

                <div class="mt-6">
                    <label for="password" class="block font-semibold text-gray-300 tracking-wide">
                        Contraseña
                    </label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" 
                           class="block mt-2 w-full bg-aromas-main border-aromas-tertiary text-white focus:border-aromas-highlight focus:ring-aromas-highlight shadow-inner rounded-lg" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-aromas-error" />
                </div>

                <div class="flex items-center justify-between mt-6">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer">
                        <input id="remember_me" type="checkbox" name="remember"
                               class="rounded border-aromas-tertiary bg-aromas-main text-aromas-highlight shadow-sm focus:ring-aromas-highlight focus:ring-offset-aromas-secondary cursor-pointer">
                        <span class="ms-2 text-sm text-gray-400 hover:text-gray-300 transition-colors">Recordarme</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm text-aromas-highlight hover:text-yellow-400 font-medium transition-colors focus:outline-none focus:underline" href="{{ route('password.request') }}">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <div class="mt-8">
                    <button type="submit" class="w-full flex justify-center items-center px-4 py-3 bg-aromas-highlight border border-transparent rounded-xl font-bold text-aromas-main uppercase tracking-widest hover:bg-yellow-400 active:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-aromas-highlight focus:ring-offset-2 focus:ring-offset-aromas-secondary transition ease-in-out duration-150 shadow-lg">
                        Iniciar Sesión
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>