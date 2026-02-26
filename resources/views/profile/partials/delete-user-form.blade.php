<section class="space-y-6">
    <header class="mb-8">
        <h2 class="text-2xl font-black text-rose-600 uppercase tracking-tighter">
            Account <span class="text-slate-800 tracking-tighter">Termination</span>
        </h2>

        <p class="mt-2 text-xs font-bold text-slate-500 uppercase tracking-[0.1em] leading-relaxed">
            {{ __('Warning: This operation is irreversible. All associated data will be purged from the system repositories.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-10 py-4 shadow-xl shadow-rose-100"
    >{{ __('Terminate Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-10 bg-white shadow-2xl rounded-[3rem] border border-slate-100">
            @csrf
            @method('delete')

            <div class="flex items-center gap-4 mb-8 pb-6 border-b border-slate-100">
                <div class="w-12 h-12 bg-rose-100 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-800 uppercase tracking-tighter">
                        Confirm <span class="text-rose-600">Deletion</span>
                    </h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Final Authorization Required</p>
                </div>
            </div>

            <p class="text-sm font-bold text-slate-600 leading-relaxed mb-8">
                {{ __('To authorize the permanent deletion of this account, please enter your security credential below. This action cannot be undone.') }}
            </p>

            <div class="space-y-2">
                <x-input-label for="password" value="{{ __('Security Credential') }}" class="sr-only" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="block w-full px-5 py-4 rounded-2xl bg-slate-50 border-slate-100 font-bold text-slate-700 focus:ring-4 focus:ring-rose-100 transition-all placeholder:text-slate-300"
                    placeholder="ENTER PASSWORD"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-10 flex justify-end gap-4">
                <x-secondary-button x-on:click="$dispatch('close')" class="px-8 py-4 border-none bg-slate-100 text-slate-500 hover:bg-slate-200">
                    {{ __('Abort') }}
                </x-secondary-button>

                <x-danger-button class="px-8 py-4 bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-200 border-none uppercase font-black tracking-tighter">
                    {{ __('Execute Deletion') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
