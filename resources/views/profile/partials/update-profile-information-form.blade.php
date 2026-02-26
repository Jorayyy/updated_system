<section>
    <header class="mb-8">
        <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tighter">
            General <span class="text-blue-600">Information</span>
        </h2>

        <p class="mt-2 text-xs font-bold text-slate-500 uppercase tracking-[0.1em]">
            {{ __("Maintain your core identity and notification channels.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-8" enctype="multipart/form-data">
        @csrf
        @method('patch')

        {{-- Identity Section --}}
        <div class="space-y-6">
            <div class="p-6 bg-slate-50/50 rounded-3xl border border-slate-100/50">
                <x-input-label for="profile_photo" :value="__('Identity Capture')" class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-4" />
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    <div class="relative">
                        @php $photoUrl = $user->getProfilePhotoUrl(); @endphp
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="Profile Photo" class="w-24 h-24 rounded-[1.8rem] object-cover border-4 border-white shadow-xl shadow-slate-200">
                        @else
                            <div class="w-24 h-24 rounded-[1.8rem] bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-3xl shadow-xl shadow-blue-100 border-4 border-white">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="block">
                            <span class="sr-only">Choose profile photo</span>
                            <input id="profile_photo" name="profile_photo" type="file" 
                                   class="block w-full text-[10px] font-black uppercase tracking-widest text-slate-500
                                          file:mr-4 file:py-2.5 file:px-6
                                          file:rounded-2xl file:border-0
                                          file:text-[10px] file:font-black file:uppercase file:tracking-[0.15em]
                                          file:bg-slate-900 file:text-white
                                          hover:file:bg-slate-800 transition-all
                                          cursor-pointer" 
                                   accept="image/*">
                        </label>
                        <p class="mt-2 text-[9px] font-bold text-slate-400 uppercase tracking-[0.1em]">Supported formats: JPG, PNG • MAX 2MB</p>
                    </div>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="name" :value="__('Display Name')" class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] ml-1 mb-1" />
                    <x-text-input id="name" name="name" type="text" class="block w-full px-5 py-3.5 rounded-2xl bg-white border-slate-200 font-bold text-slate-700 focus:ring-4 focus:ring-blue-100 transition-all" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Contact Email')" class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] ml-1 mb-1" />
                    <x-text-input id="email" name="email" type="email" class="block w-full px-5 py-3.5 rounded-2xl bg-white border-slate-200 font-bold text-slate-700 focus:ring-4 focus:ring-blue-100 transition-all" :value="old('email', $user->email)" required autocomplete="username" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="mt-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-rose-500">
                                {{ __('Email Unverified.') }}

                                <button form="send-verification" class="ml-2 underline text-slate-600 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                    {{ __('Resend Link') }}
                                </button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-1 font-bold text-[9px] uppercase tracking-widest text-emerald-600">
                                    {{ __('A new link has been dispatched.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
            <x-primary-button class="px-10 py-4 shadow-xl shadow-blue-100">
                Update Identity
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="flex items-center gap-2 text-emerald-600"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">{{ __('Changes Saved') }}</span>
                </div>
            @endif
        </div>
    </form>
</section>
