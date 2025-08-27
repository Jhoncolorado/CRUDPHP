<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Galería de Imágenes</title>
	<style>
		:root { --bg:#f5f6f8; --card:#ffffff; --text:#1f2937; --muted:#6b7280; --border:#e5e7eb; --primary:#0d6efd; --primary-600:#0b5ed7; --danger:#dc3545; --danger-600:#bb2d3b; --ok:#16a34a; }
		html, body { margin:0; padding:0; font-family:Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif; background:var(--bg); color:var(--text); }
		.container { max-width:1100px; margin:40px auto; padding:0 16px; }
		.card { background:var(--card); border:1px solid var(--border); border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.05); overflow:hidden; }
		.header { padding:20px 24px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px; }
		.header h1 { margin:0; font-size:20px; font-weight:600; }
		.header .sub { color:var(--muted); font-size:13px; }
		.form { padding:20px 24px; display:flex; gap:12px; flex-wrap:wrap; align-items:center; background:linear-gradient(180deg, #fafbff 0%, #ffffff 100%); }
		.input { padding:10px 12px; border:1px solid var(--border); border-radius:10px; background:#fff; }
		.input[type="file"] { padding:8px; }
		.input[type="text"] { min-width:300px; }
		.button { padding:10px 14px; border-radius:10px; border:1px solid transparent; cursor:pointer; transition:all .15s ease; font-weight:600; }
		.button.primary { background:var(--primary); color:#fff; }
		.button.primary:hover { background:var(--primary-600); }
		.button.ghost { background:#fff; border-color:var(--border); color:var(--text); }
		.button.danger { background:var(--danger); color:#fff; }
		.button.danger:hover { background:var(--danger-600); }
		.grid { padding:20px 24px; display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:18px; }
		.item { border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff; display:flex; flex-direction:column; }
		.thumb { aspect-ratio: 1 / 1; width:100%; object-fit:cover; background:#f1f5f9; }
		.meta { padding:12px; font-size:12px; color:var(--muted); display:flex; flex-direction:column; gap:6px; }
		.actions { padding:12px; display:flex; gap:8px; }
		.empty { padding:28px; text-align:center; color:var(--muted); }
		footer { text-align:center; padding:20px; color:var(--muted); font-size:12px; }

		/* Toasts */
		.toasts { position:fixed; right:20px; top:20px; display:flex; flex-direction:column; gap:10px; z-index:9999; }
		.toast { min-width:260px; max-width:360px; background:#fff; border:1px solid var(--border); box-shadow:0 10px 20px rgba(0,0,0,.08); border-radius:12px; padding:12px 14px; display:flex; gap:10px; align-items:flex-start; animation:slideIn .18s ease-out; }
		.toast.ok { border-left:6px solid var(--ok); }
		.toast.err { border-left:6px solid var(--danger); }
		.toast .t-title { font-weight:700; font-size:13px; }
		.toast .t-msg { font-size:13px; color:var(--muted); margin-top:2px; }
		.toast .t-close { margin-left:auto; cursor:pointer; color:var(--muted); border:none; background:transparent; font-size:16px; line-height:1; }
		@keyframes slideIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
	</style>
</head>
<body>
	<div class="toasts" id="toasts"></div>
	<div class="container">
		<div class="card">
			<div class="header">
				<div>
					<h1>Gestión de Imágenes</h1>
					<div class="sub">Sube, visualiza y elimina imágenes. API CodeIgniter 4</div>
				</div>
				<div>
					<button id="refresh" class="button ghost">Actualizar</button>
				</div>
			</div>
			<form id="uploadForm" class="form">
				<input id="file" name="image" class="input" type="file" accept="image/*" required />
				<input id="enlace" name="enlace" class="input" type="text" placeholder="Enlace (opcional)" />
				<button class="button primary" type="submit">Subir imagen</button>
				<span id="status" style="color:var(--muted)"></span>
			</form>
			<div id="grid" class="grid"></div>
			<div id="empty" class="empty" style="display:none">No hay imágenes cargadas.</div>
		</div>
		<footer>API REST CodeIgniter · CRUD Imágenes</footer>
	</div>
	<script>
		const API = `${location.origin}/CRUD-PHP/public`;
		const grid = document.getElementById('grid');
		const empty = document.getElementById('empty');
		const statusEl = document.getElementById('status');
		const toasts = document.getElementById('toasts');

		function showToast(type, title, msg) {
			const el = document.createElement('div');
			el.className = `toast ${type}`;
			el.innerHTML = `<div><div class="t-title">${title}</div><div class="t-msg">${msg}</div></div><button class="t-close" aria-label="Cerrar">×</button>`;
			const closer = el.querySelector('.t-close');
			closer.onclick = () => el.remove();
			toasts.appendChild(el);
			setTimeout(() => el.remove(), 4000);
		}

		async function fetchImages() {
			const res = await fetch(`${API}/images`);
			const data = await res.json();
			render(data);
		}

		function render(items) {
			grid.innerHTML = '';
			if (!items || items.length === 0) {
				empty.style.display = 'block';
				return;
			}
			empty.style.display = 'none';
			for (const it of items) {
				const card = document.createElement('div');
				card.className = 'item';
				const img = document.createElement('img');
				img.className = 'thumb';
				img.src = `${API}/uploads/${it.filename}`;
				img.alt = it.filename;
				const meta = document.createElement('div');
				meta.className = 'meta';
				meta.innerHTML = `<div><strong>Archivo:</strong> ${it.filename}</div>
					<div><strong>Enlace:</strong> ${it.enlace ?? ''}</div>
					<div><strong>Fecha:</strong> ${it.created_at}</div>`;
				const actions = document.createElement('div');
				actions.className = 'actions';
				const openBtn = document.createElement('a');
				openBtn.className = 'button ghost';
				openBtn.textContent = 'Ver';
				openBtn.href = `${API}/uploads/${it.filename}`;
				openBtn.target = '_blank';
				const delBtn = document.createElement('button');
				delBtn.className = 'button danger';
				delBtn.textContent = 'Eliminar';
				delBtn.onclick = async () => {
					if (!confirm('¿Eliminar esta imagen?')) return;
					try {
						const r = await fetch(`${API}/images/${it.id}`, { method: 'DELETE' });
						if (!r.ok) throw new Error('No se pudo eliminar');
						showToast('ok', 'Eliminada', 'La imagen se eliminó correctamente.');
						await fetchImages();
					} catch (e) {
						showToast('err', 'Error', e.message || 'Error al eliminar.');
					}
				};
				actions.appendChild(openBtn);
				actions.appendChild(delBtn);
				card.appendChild(img);
				card.appendChild(meta);
				card.appendChild(actions);
				grid.appendChild(card);
			}
		}

		document.getElementById('refresh').onclick = fetchImages;

		document.getElementById('uploadForm').addEventListener('submit', async (e) => {
			e.preventDefault();
			statusEl.textContent = 'Subiendo...';
			const form = new FormData(e.target);
			try {
				const res = await fetch(`${API}/images`, { method: 'POST', body: form });
				if (!res.ok) throw new Error('No se pudo subir la imagen');
				statusEl.textContent = '';
				showToast('ok', 'Subida', 'La imagen se subió correctamente.');
				e.target.reset();
				await fetchImages();
			} catch (err) {
				statusEl.textContent = '';
				showToast('err', 'Error', err.message || 'Error al subir.');
			}
		});

		fetchImages();
	</script>
</body>
</html>
