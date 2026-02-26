<section>
    <header class="mb-8">
        <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tighter">
            Security <span class="text-blue-600">Protocol</span>
        </h2>

        <p class="mt-2 text-xs font-bold text-slate-500 uppercase tracking-[0.1em]">
            {{ __('Maintain high-entropy access credentials for maximum account safety.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div class="space-y-4">
            <div>
                <x-input-label for="update_password_current_password" :value="__('Current Password')" class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] ml-1 mb-1" />
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="block w-full px-5 py-3.5 rounded-2xl bg-white border-slate-200 font-bold text-slate-700 focus:ring-4 focus:ring-blue-100 transition-all placeholder:text-slate-300" autocomplete="current-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password" :value="__('New Password')" class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] ml-1 mb-1" />
                <x-text-input id="update_password_password" name="password" type="password" class="block w-full px-5 py-3.5 rounded-2xl bg-white border-slate-200 font-bold text-slate-700 focus:ring-4 focus:ring-blue-100 transition-all placeholder:text-slate-300" autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" :value="__('Verify New Password')" class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] ml-1 mb-1" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="block w-full px-5 py-3.5 rounded-2xl bg-white border-slate-200 font-bold text-slate-700 focus:ring-4 focus:ring-blue-100 transition-all placeholder:text-slate-300" autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
            <x-primary-button class="px-10 py-4 shadow-xl shadow-slate-200">
                Update Password
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="flex items-center gap-2 text-emerald-600"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">{{ __('Updated') }}</span>
                </div>
            @endif
        </div>
    </form>
</section>
