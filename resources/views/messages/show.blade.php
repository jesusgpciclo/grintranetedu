@extends('layouts.app')

@section('title', $message->subject)

@section('content')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
    /* Quill Dark Theme Adjustments for Reply */
    .chat-input-container .ql-toolbar.ql-snow {
        border: 1px solid rgba(255,255,255,0.1) !important;
        background: rgba(255,255,255,0.05) !important;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
        padding: 4px 8px;
    }
    .chat-input-container .ql-container.ql-snow {
        border: 1px solid rgba(255,255,255,0.1) !important;
        background: rgba(255,255,255,0.03) !important;
        border-bottom-left-radius: 1rem;
        border-bottom-right-radius: 1rem;
        font-family: 'Outfit', sans-serif;
        font-size: 0.95rem;
    }
    .chat-input-container .ql-editor {
        color: #fff !important;
        min-height: 80px;
        max-height: 200px;
    }
    .chat-input-container .ql-editor.ql-blank::before {
        color: rgba(255,255,255,0.3) !important;
        font-style: normal;
    }
    .chat-input-container .ql-snow .ql-stroke { stroke: #fff !important; }
    .chat-input-container .ql-snow .ql-fill { fill: #fff !important; }

    /* Message Body Styles */
    .message-bubble div p { margin-bottom: 0.5rem; }
    .message-bubble div p:last-child { margin-bottom: 0; }
    .message-bubble div ul { list-style-type: disc !important; margin-left: 1.5rem !important; margin-bottom: 0.5rem; }
    .message-bubble div ol { list-style-type: decimal !important; margin-left: 1.5rem !important; margin-bottom: 0.5rem; }
    .message-bubble div li { margin-bottom: 0.25rem; }
    .message-bubble div a { color: var(--primary); text-decoration: underline; }

    /* Fix toolbar icons and add emoji style */
    .ql-snow .ql-stroke { stroke: #fff !important; }
    .ql-snow .ql-fill { fill: #fff !important; }
    .ql-snow .ql-picker { color: #fff !important; }
    .ql-emoji {
        background: none !important;
        border: none !important;
        cursor: pointer;
    }

    /* Active State for Toolbar Buttons */
    .ql-snow.ql-toolbar button:hover,
    .ql-snow .ql-toolbar button:hover,
    .ql-snow.ql-toolbar button.ql-active,
    .ql-snow .ql-toolbar button.ql-active {
        background: rgba(255,255,255,0.1) !important;
        border-radius: 4px;
    }
    .ql-snow.ql-toolbar button.ql-active .ql-stroke,
    .ql-snow .ql-toolbar button.ql-active .ql-stroke,
    .ql-snow.ql-toolbar button:hover .ql-stroke,
    .ql-snow .ql-toolbar button:hover .ql-stroke {
        stroke: var(--primary, #007bff) !important;
        stroke-width: 2.5px;
    }
    .ql-snow.ql-toolbar button.ql-active .ql-fill,
    .ql-snow .ql-toolbar button.ql-active .ql-fill,
    .ql-snow.ql-toolbar button:hover .ql-fill,
    .ql-snow .ql-toolbar button:hover .ql-fill {
        fill: var(--primary, #007bff) !important;
    }
    .ql-snow .ql-picker-label:hover,
    .ql-snow .ql-picker-label.ql-active {
        color: var(--primary, #007bff) !important;
    }
    .ql-snow .ql-picker-label:hover .ql-stroke,
    .ql-snow .ql-picker-label.ql-active .ql-stroke {
        stroke: var(--primary, #007bff) !important;
    }
</style>

<div class="content-header" style="margin-bottom: 2rem;">
    <a href="{{ route('messages.index') }}" style="color: var(--primary); text-decoration: none; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
        ← Volver a Mensajes
    </a>
    <h1 style="font-size: 1.875rem; font-weight: 700; color: #fff; margin: 0;">{{ $message->subject }}</h1>
</div>

<div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; min-height: 70vh;">
    <!-- Chat History -->
    <div class="chat-history" id="chatHistory">
        <!-- Original Message -->
        @php $isMe = $message->sender_id === Auth::id(); @endphp
        <div class="message-row {{ $isMe ? 'message-sent' : 'message-received' }}">
            <div class="message-content">
                <div class="message-meta">
                    <strong>{{ $isMe ? 'Tú' : $message->sender->name }}</strong>
                    <span>{{ $message->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="message-bubble">
                    <div class="rich-content">{!! $message->body !!}</div>
                </div>
            </div>
        </div>

        <!-- Replies -->
        @foreach($message->replies as $reply)
            @php $isMe = $reply->sender_id === Auth::id(); @endphp
            <div class="message-row {{ $isMe ? 'message-sent' : 'message-received' }}">
                <div class="message-content">
                    <div class="message-meta">
                        <strong>{{ $isMe ? 'Tú' : $reply->sender->name }}</strong>
                        <span>{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="message-bubble">
                        <div class="rich-content">{!! $reply->body !!}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Reply Form -->
    <div class="chat-input-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; padding: 0 0.25rem;">
            <span style="font-weight: 700; font-size: 0.9rem; color: var(--text-heading);">Responder</span>
            <button type="button" onclick="openDocManagerModal()" class="btn btn-secondary btn-sm" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8; border-color: rgba(56, 189, 248, 0.2); font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                📂 Adjuntar desde Gestor Documental
            </button>
        </div>
        <form action="{{ route('messages.store') }}" method="POST" id="replyForm">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $message->id }}">
            <input type="hidden" name="receiver_id" value="{{ $message->sender_id === Auth::id() ? $message->receiver_id : $message->sender_id }}">
            <input type="hidden" name="subject" value="Re: {{ $message->subject }}">
            
            <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                <div style="flex: 1; position: relative;">
                    <span id="draft-status" style="position: absolute; top: -1.25rem; right: 0; font-size: 0.7rem; color: var(--text-muted); opacity: 0; transition: opacity 0.3s;">Borrador guardado</span>
                    <div id="reply-editor"></div>
                    <textarea name="body" id="reply_body" style="display: none;"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 0 1.25rem; border-radius: 1rem; height: 45px; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; margin-top: 40px;">
                    <span>Enviar</span>
                </button>
            </div>
        </form>

    <!-- Footer Actions (Delete, etc) -->
    <div style="padding: 1.5rem 2rem; background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.75rem; color: var(--text-muted);">
            Conversación entre {{ $message->sender->name }} y {{ $message->receiver->name }}
        </span>
        <form action="{{ route('messages.destroy', $message) }}" method="POST" onsubmit="return confirm('¿Eliminar esta conversación completa? No podrás deshacer esta acción.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-delete-thread" style="background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.85rem; opacity: 0.8; display: flex; align-items: center; gap: 0.4rem; transition: opacity 0.2s;">
                <span>🗑️</span>
                <span style="text-decoration: underline;">Eliminar conversación</span>
            </button>
        </form>
    </div>
</div>

<!-- Document Selector Modal -->
<div id="modal-doc-manager" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.65); backdrop-filter: blur(4px); z-index: 999; align-items: center; justify-content: center; padding: 1rem;">
    <div class="card" style="width: 100%; max-width: 600px; margin: 0; box-shadow: var(--shadow-lg); padding: 1.5rem; background: var(--bg-card); border-radius: 0.75rem; border: 1px solid var(--border);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: var(--text-heading);">📂 Adjuntar Documento</h3>
            <button type="button" onclick="closeDocManagerModal()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.2rem;">✕</button>
        </div>

        <div class="form-group" style="margin-bottom: 1rem;">
            <input type="text" id="doc-search-input" placeholder="Buscar documento por título..." class="form-control" oninput="searchDocs()">
        </div>

        <div id="doc-list-container" style="max-height: 300px; overflow-y: auto; margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; padding-right: 0.25rem;">
            <p style="text-align: center; color: var(--text-muted); padding: 1rem;">Cargando documentos...</p>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
            <button type="button" onclick="closeDocManagerModal()" class="btn btn-secondary">Cancelar</button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    let docFetchTimeout = null;

    function openDocManagerModal() {
        document.getElementById('modal-doc-manager').style.display = 'flex';
        document.getElementById('doc-search-input').value = '';
        loadDocs();
    }

    // Helper functions for document attachments
    function closeDocManagerModal() {
        document.getElementById('modal-doc-manager').style.display = 'none';
    }

    function searchDocs() {
        clearTimeout(docFetchTimeout);
        docFetchTimeout = setTimeout(loadDocs, 300);
    }

    function loadDocs() {
        const searchVal = document.getElementById('doc-search-input').value;
        const container = document.getElementById('doc-list-container');
        container.innerHTML = '<p style="text-align: center; color: var(--text-muted); padding: 1rem;">Buscando...</p>';

        fetch(`/api/documentos-list?search=${encodeURIComponent(searchVal)}`)
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    container.innerHTML = '<p style="text-align: center; color: var(--text-muted); padding: 1rem;">No se encontraron documentos.</p>';
                    return;
                }

                container.innerHTML = '';
                data.forEach(doc => {
                    const item = document.createElement('div');
                    item.style.cssText = 'padding: 0.75rem; background: var(--bg-hover); border: 1px solid var(--border); border-radius: 0.5rem; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s; cursor: pointer;';
                    item.onclick = () => attachDocToEditor(doc.titulo, doc.url);
                    item.onmouseenter = () => item.style.borderColor = 'var(--primary)';
                    item.onmouseleave = () => item.style.borderColor = 'var(--border)';

                    item.innerHTML = `
                        <div style="font-weight: 600; color: var(--text-heading); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 420px;">
                            📄 ${doc.titulo}
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Adjuntar</button>
                    `;
                    container.appendChild(item);
                });
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<p style="text-align: center; color: var(--danger); padding: 1rem;">Error al cargar documentos.</p>';
            });
    }

    function attachDocToEditor(title, url) {
        if (typeof quill !== 'undefined') {
            const range = quill.getSelection(true);
            quill.insertText(range.index, `📄 ${title}`, {
                'link': url,
                'bold': true
            });
            quill.insertText(range.index + title.length + 2, ' ');
            quill.setSelection(range.index + title.length + 3);
        }
        closeDocManagerModal();
    }
    document.addEventListener('DOMContentLoaded', function() {
        const chatHistory = document.getElementById('chatHistory');
        chatHistory.scrollTop = chatHistory.scrollHeight;

        // Initialize Quill for Reply
        var quill = new Quill('#reply-editor', {
            theme: 'snow',
            placeholder: 'Escribe tu respuesta...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'clean']
                ]
            }
        });

        const replyBody = document.getElementById('reply_body');
        const draftStatus = document.getElementById('draft-status');
        const messageId = '{{ $message->id }}';
        const storageKey = 'reply_draft_' + messageId;

        // Sync Quill with textarea on submit (Essential)
        document.getElementById('replyForm').addEventListener('submit', function(e) {
            replyBody.value = quill.root.innerHTML;
            if (quill.getText().trim().length === 0) {
                e.preventDefault();
                alert('La respuesta no puede estar vacía');
                return false;
            }
            localStorage.removeItem(storageKey);
        });

        // Tooltips
        setTimeout(() => {
            const toolbar = document.querySelector('.ql-toolbar');
            if (toolbar) {
                const toolbarTooltips = {
                    'bold': 'Negrita', 'italic': 'Cursiva', 'underline': 'Subrayado', 'strike': 'Tachado',
                    'list[value="bullet"]': 'Lista de viñetas', 'list[value="ordered"]': 'Lista numerada',
                    'link': 'Insertar enlace', 'clean': 'Limpiar formato', 'color': 'Color de texto', 'background': 'Color de fondo'
                };
                Object.keys(toolbarTooltips).forEach(selector => {
                    const button = toolbar.querySelector(`.ql-${selector}`);
                    if (button) button.setAttribute('title', toolbarTooltips[selector]);
                });
                // Pickers
                toolbar.querySelectorAll('.ql-picker').forEach(picker => {
                    if (picker.classList.contains('ql-color')) picker.setAttribute('title', 'Color de texto');
                    if (picker.classList.contains('ql-background')) picker.setAttribute('title', 'Color de fondo');
                });
            }
        }, 500);

        const clearReplyDraft = () => {
            if (confirm('¿Estás seguro de que quieres descartar este borrador?')) {
                localStorage.removeItem(storageKey);
                quill.setContents([]);
                draftStatus.style.opacity = 1;
                draftStatus.textContent = 'Borrador eliminado';
                setTimeout(() => { draftStatus.style.opacity = 0; }, 2000);
            }
        };

        // Add clear button next to the reply editor
        const replyEditor = document.getElementById('reply-editor');
        if (replyEditor) {
            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.innerHTML = '✕';
            clearBtn.title = 'Descartar borrador';
            clearBtn.style.cssText = 'position: absolute; top: -25px; right: 0; background: none; border: none; color: #ef4444; font-size: 1.2rem; cursor: pointer; padding: 5px; line-height: 1;';
            clearBtn.onclick = clearReplyDraft;
            replyEditor.parentNode.appendChild(clearBtn);
        }

        // Auto-save and Loading
        quill.on('text-change', () => {
            const text = quill.getText().trim();
            if (text.length > 0) {
                localStorage.setItem(storageKey, quill.root.innerHTML);
                draftStatus.style.opacity = 1;
                draftStatus.textContent = 'Borrador guardado';
                setTimeout(() => { draftStatus.style.opacity = 0.5; }, 2000);
            } else {
                localStorage.removeItem(storageKey);
                draftStatus.style.opacity = 0;
            }
        });

        if (localStorage.getItem(storageKey)) {
            const savedBody = localStorage.getItem(storageKey);
            if (savedBody && savedBody !== '<p><br></p>') {
                quill.root.innerHTML = savedBody;
                draftStatus.style.opacity = 1;
                draftStatus.textContent = 'Borrador cargado';
            }
        }

        // Thread deletion with logging
        const deleteThreadForm = document.querySelector('form[action*="destroy"]');
        if (deleteThreadForm) {
            deleteThreadForm.addEventListener('submit', function(e) {
                console.log('Delete thread submitted');
            });
        }
    });
</script>
@endpush
@endsection
