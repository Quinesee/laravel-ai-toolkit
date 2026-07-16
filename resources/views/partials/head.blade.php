<meta charset="utf-8" />
<meta
    content="width=device-width, initial-scale=1.0"
    name="viewport"
/>
<meta
    content="{{ csrf_token() }}"
    name="csrf-token"
/>

<title>{{ $title ?? config('app.name') }}</title>

<link
    href="/favicon.ico"
    rel="icon"
    sizes="any"
>
<link
    href="/favicon.svg"
    rel="icon"
    type="image/svg+xml"
>
<link
    href="/apple-touch-icon.png"
    rel="apple-touch-icon"
>

<link
    href="https://fonts.bunny.net"
    rel="preconnect"
>
<link
    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
    rel="stylesheet"
/>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ticketChatDemo', (ticketId, initialResponse = '') => ({
            ticketId,
            prompt: '',
            response: initialResponse,
            async send() {
                const message = this.prompt.trim();

                if (message.length < 3) {
                    return;
                }

                this.prompt = '';

                try {
                    const response = await fetch(`/tickets/${this.ticketId}/ai/chat`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            message
                        }),
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message);
                    }

                    this.response = data.message ?? '';
                } catch (error) {
                    console.error('Error sending message:', error);
                }
            }
        }));

        Alpine.data('ticketDraftDemo', (ticketId, initialDraft = '') => ({
            ticketId,
            draft: initialDraft,
            prompt: '',
            controller: null,
            async streamDraft() {
                this.draft = '';
                this.controller = new AbortController();

                const response = await fetch(
                    `/tickets/${this.ticketId}/ai/draft-reply/stream`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'text/event-stream',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').content,
                        },
                        signal: this.controller.signal
                    });

                const reader = response.body.getReader();
                const decoder = new TextDecoder('utf-8');
                let buffer = '';

                while (true) {
                    const {
                        value,
                        done
                    } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, {
                        stream: true
                    });

                    const parts = buffer.split('\n\n');
                    buffer = parts.pop() ?? '';

                    for (const part of parts) {
                        if (!part.startsWith('data:')) continue;
                        const payload = part.replace('data: ', '').trim();

                        if (payload === '[DONE]') return;

                        try {
                            const event = JSON.parse(payload);
                            if (event.type === 'text_delta') {
                                this.draft += event.delta;
                            }
                        } catch (error) {
                            console.error('Error parsing event:', error);
                        }
                    }
                }
            },
            cancelStream() {
                this.controller?.abort();
            },
            insertIntoReply() {
                const replyBox = document.querySelector('[data-ticket-reply]');

                replyBox.value = this.draft;
            }
        }));
    });
</script>
