@extends('layouts.app')

@section('title', 'Nuevo Mensaje')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
    /* Quill Dark Theme Adjustments */
    .ql-toolbar.ql-snow {
        border: 1px solid rgba(255,255,255,0.1) !important;
        background: rgba(255,255,255,0.05) !important;
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }
    .ql-container.ql-snow {
        border: 1px solid rgba(255,255,255,0.1) !important;
        background: rgba(255,255,255,0.02) !important;
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        font-family: 'Outfit', sans-serif;
        font-size: 1rem;
        min-height: 200px;
    }
    .ql-editor {
        color: #fff !important;
    }
    .ql-editor.ql-blank::before {
        color: rgba(255,255,255,0.3) !important;
        font-style: normal;
    }
    .ql-snow .ql-stroke {
        stroke: #fff !important;
    }
    .ql-snow .ql-fill {
        fill: #fff !important;
    }
    .ql-snow .ql-picker {
        color: #fff !important;
    }
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
    
    /* Select Dropdown Styles */
    select {
        background-color: rgba(255, 255, 255, 0.05) !important;
        color: #fff !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 0.5rem;
        padding: 0.5rem;
    }
    select option {
        background-color: #1a1a1a !important;
        color: #fff !important;
    }
    select:focus {
        outline: none;
        border-color: var(--primary) !important;
    }
</style>

<div class="content-header" style="margin-bottom: 2rem;">
    <a href="{{ route('messages.index') }}" style="color: var(--primary); text-decoration: none; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
        ← Volver a recibidos
    </a>
    <h1 style="font-size: 1.875rem; font-weight: 700; color: #fff; margin: 0;">Nuevo Mensaje</h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="{{ route('messages.store') }}" method="POST" id="createMessageForm" style="padding: 1.5rem;">
        @csrf
        
        <div style="margin-bottom: 2rem; background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.05);">
            <label style="display: block; margin-bottom: 1rem; color: var(--primary); font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em;">Enviar a:</label>
            
            <div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: #fff;">
                    <input type="radio" name="dest_type" value="individual" checked onclick="toggleDest('individual')" style="accent-color: var(--primary);"> Usuario
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: #fff;">
                    <input type="radio" name="dest_type" value="role" onclick="toggleDest('role')" style="accent-color: var(--primary);"> Rol / Cargo
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: #fff;">
                    <input type="radio" name="dest_type" value="group" onclick="toggleDest('group')" style="accent-color: var(--primary);"> Grupo / Clase
                </label>
            </div>

            <div id="dest-individual" class="dest-field">
                <select name="receiver_id" id="receiver_id" class="form-control" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;">
                    <option value="" disabled selected>Selecciona un usuario...</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>

            <div id="dest-role" class="dest-field" style="display: none;">
                <select name="role_id" id="role_id" class="form-control" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;">
                    <option value="" disabled selected>Selecciona un rol...</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="dest-group" class="dest-field" style="display: none;">
                <select name="group_id" id="group_id" class="form-control" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;">
                    <option value="" disabled selected>Selecciona un grupo...</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="message_subject" style="display: block; margin-bottom: 0.5rem; color: #fff; font-weight: 500;">Asunto</label>
            <input type="text" name="subject" id="message_subject" class="form-control" placeholder="¿De qué trata el mensaje? (Opcional)" style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;">
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <label for="body" style="color: #fff; font-weight: 500;">Mensaje</label>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="button" onclick="openDocManagerModal()" class="btn btn-secondary btn-sm" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8; border-color: rgba(56, 189, 248, 0.2); font-size: 0.8rem; padding: 0.35rem 0.75rem;">
                        📂 Adjuntar desde Gestor Documental
                    </button>
                    <span id="draft-status" style="font-size: 0.75rem; color: var(--text-muted); opacity: 0;">Guardado como borrador</span>
                </div>
            </div>
            
            <div style="position: relative;">
                <div id="editor-container"></div>
                <textarea name="body" id="message_body" style="display: none;"></textarea>
            </div>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-weight: 700;">
                Enviar Mensaje
            </button>
            <a href="{{ route('messages.index') }}" class="btn btn-secondary" style="padding: 0.75rem 2rem; display: flex; align-items: center; text-decoration: none;">
                Cancelar
            </a>
        </div>
    </form>
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

    function toggleDest(type) {
        document.querySelectorAll('.dest-field').forEach(el => el.style.display = 'none');
        document.getElementById('dest-' + type).style.display = 'block';
        
        if(type !== 'individual') document.getElementById('receiver_id').value = '';
        if(type !== 'role') document.getElementById('role_id').value = '';
        if(type !== 'group') document.getElementById('group_id').value = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Quill
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Escribe tu mensaje aquí...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'clean']
                ]
            }
        });

        const bodyTextarea = document.getElementById('message_body');
        const subjectInput = document.getElementById('message_subject');
        const draftStatus = document.getElementById('draft-status');

        // Sync Quill with textarea on submit (Essential)
        document.getElementById('createMessageForm').addEventListener('submit', function(e) {
            bodyTextarea.value = quill.root.innerHTML;
            
            // Check if body is empty
            if (quill.getText().trim().length === 0) {
                e.preventDefault();
                alert('El mensaje no puede estar vacío');
                return false;
            }

            // Check if subject is empty
            if (subjectInput.value.trim() === '') {
                if (!confirm('Has dejado el asunto vacío. ¿Seguro que quieres enviar el mensaje sin asunto?')) {
                    e.preventDefault();
                    subjectInput.focus();
                    return false;
                }
            }

            localStorage.removeItem('msg_create_body');
            localStorage.removeItem('msg_create_subject');
        });

        // Tooltips (Optional)
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
            }
        }, 300);

        const clearDraft = () => {
            if (confirm('¿Estás seguro de que quieres descartar este borrador?')) {
                localStorage.removeItem('msg_create_body');
                localStorage.removeItem('msg_create_subject');
                quill.setContents([]);
                subjectInput.value = '';
                draftStatus.style.opacity = 1;
                draftStatus.textContent = 'Borrador eliminado';
                setTimeout(() => { draftStatus.style.opacity = 0; }, 2000);
            }
        };

        // Add clear button to UI (dynamically or manually)
        const btnContainer = document.querySelector('div[style="display: flex; gap: 1rem;"]');
        if (btnContainer) {
            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'btn btn-secondary';
            clearBtn.style.cssText = 'padding: 0.75rem 2rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);';
            clearBtn.textContent = 'Descartar Borrador';
            clearBtn.onclick = clearDraft;
            btnContainer.appendChild(clearBtn);
        }

        // Auto-save and Loading
        const autoSave = () => {
            const text = quill.getText().trim();
            const subject = subjectInput.value.trim();
            
            if (text.length > 0 || subject.length > 0) {
                localStorage.setItem('msg_create_body', quill.root.innerHTML);
                localStorage.setItem('msg_create_subject', subjectInput.value);
                draftStatus.style.opacity = 1;
                draftStatus.textContent = 'Borrador guardado';
                setTimeout(() => { draftStatus.style.opacity = 0.5; }, 2000);
            } else {
                localStorage.removeItem('msg_create_body');
                localStorage.removeItem('msg_create_subject');
                draftStatus.style.opacity = 0;
            }
        };

        quill.on('text-change', autoSave);
        subjectInput.addEventListener('input', autoSave);

        if (localStorage.getItem('msg_create_body')) {
            const savedBody = localStorage.getItem('msg_create_body');
            if (savedBody && savedBody !== '<p><br></p>') {
                quill.root.innerHTML = savedBody;
                draftStatus.style.opacity = 1;
                draftStatus.textContent = 'Borrador cargado';
            }
        }
        if (localStorage.getItem('msg_create_subject')) {
            subjectInput.value = localStorage.getItem('msg_create_subject');
        }
    });
</script>
@endpush
@endsection
