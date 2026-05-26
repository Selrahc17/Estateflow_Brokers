
    {{-- Floating AI Chatbot Widget --}}
    <div class="fixed bottom-6 right-6 z-50"
         x-data="{ chatOpen: false, message: '', messages: [{from:'ai',text:'Hi! I am EstateFlow AI. Ask me anything about properties, reservations, or payments!'}] }">

        {{-- Chat Window --}}
        <div x-show="chatOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-4"
             class="absolute bottom-16 right-0 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-stone-200 overflow-hidden mb-2">

            {{-- Header --}}
            <div class="bg-gradient-to-r from-stone-800 to-amber-800 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-amber-500 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-semibold text-sm">EstateFlow AI</p>
                        <p class="text-amber-300 text-xs flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full inline-block animate-pulse"></span>
                            Online &middot; Always available
                        </p>
                    </div>
                </div>
                <button type="button" @click.stop="chatOpen = false" class="text-white/70 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Messages --}}
            <div class="h-72 overflow-y-auto p-4 space-y-3 bg-stone-50">
                <template x-for="(msg, i) in messages" :key="i">
                    <div :class="msg.from === 'user' ? 'flex flex-row-reverse items-end gap-2' : 'flex items-end gap-2'">
                        <template x-if="msg.from === 'ai'">
                            <div class="w-6 h-6 bg-amber-600 rounded-full flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                            </div>
                        </template>
                        <div :class="msg.from === 'user' ? 'bg-amber-600 text-white rounded-2xl rounded-br-none' : 'bg-white border border-stone-200 text-stone-700 rounded-2xl rounded-bl-none'"
                             class="px-3 py-2 max-w-xs shadow-sm">
                            <p class="text-xs leading-relaxed" x-text="msg.text"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Quick Replies --}}
            <div class="px-4 py-2 border-t border-stone-100 bg-white">
                <div class="flex gap-1.5 flex-wrap">
                    <button type="button" @click.stop="messages.push({from:'user',text:'Payment due?'});setTimeout(()=>{messages.push({from:'ai',text:'Visit My Payments in your account for payment details and schedules.'})},800)" class="text-xs bg-stone-100 hover:bg-amber-50 hover:text-amber-700 text-stone-500 px-2.5 py-1 rounded-full transition border border-transparent hover:border-amber-200">Payment due?</button>
                    <button type="button" @click.stop="messages.push({from:'user',text:'My documents'});setTimeout(()=>{messages.push({from:'ai',text:'Upload and view your documents in the My Documents section of your account.'})},800)" class="text-xs bg-stone-100 hover:bg-amber-50 hover:text-amber-700 text-stone-500 px-2.5 py-1 rounded-full transition border border-transparent hover:border-amber-200">My documents</button>
                    <button type="button" @click.stop="messages.push({from:'user',text:'Lot details'});setTimeout(()=>{messages.push({from:'ai',text:'Browse available lots and properties in the Properties section.'})},800)" class="text-xs bg-stone-100 hover:bg-amber-50 hover:text-amber-700 text-stone-500 px-2.5 py-1 rounded-full transition border border-transparent hover:border-amber-200">Lot details</button>
                    <button type="button" @click.stop="messages.push({from:'user',text:'Contact broker'});setTimeout(()=>{messages.push({from:'ai',text:'You can message your broker through the Chat section in your account.'})},800)" class="text-xs bg-stone-100 hover:bg-amber-50 hover:text-amber-700 text-stone-500 px-2.5 py-1 rounded-full transition border border-transparent hover:border-amber-200">Contact broker</button>
                </div>
            </div>

            {{-- Input --}}
            <div class="p-3 border-t border-stone-100 bg-white">
                <div class="flex gap-2">
                    <input x-model="message"
                           @keydown.enter.stop="if(message.trim()){messages.push({from:'user',text:message});message='';setTimeout(()=>{messages.push({from:'ai',text:'Thanks! For detailed assistance please visit the relevant section in your account.'})},800)}"
                           type="text" placeholder="Ask anything..."
                           class="flex-1 border border-stone-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-amber-400 bg-stone-50">
                    <button type="button"
                            @click.stop="if(message.trim()){messages.push({from:'user',text:message});message='';setTimeout(()=>{messages.push({from:'ai',text:'Thanks! For detailed assistance please visit the relevant section in your account.'})},800)}"
                            class="w-8 h-8 bg-amber-600 hover:bg-amber-700 text-white rounded-xl flex items-center justify-center transition shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </div>
                <p class="text-center text-xs text-stone-400 mt-2">
                    <a href="{{ route('client.account.chat') }}" class="hover:text-amber-600 transition">Open full AI Assistant &rarr;</a>
                </p>
            </div>
        </div>

        {{-- Toggle Button --}}
        <button type="button" @click.stop="chatOpen = !chatOpen"
            class="w-14 h-14 bg-amber-600 hover:bg-amber-700 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95">
            <svg x-show="!chatOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <svg x-show="chatOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

    </div>

