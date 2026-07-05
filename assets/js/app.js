/* Lista de Regalos · Baby Shower — interacción pública */
(function () {
    'use strict';

    /* ---------- Revelado al entrar en viewport ---------- */
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -6% 0px' });

    document.querySelectorAll('.reveal').forEach(function (el, i) {
        el.style.transitionDelay = Math.min(i % 6 * 70, 350) + 'ms';
        observer.observe(el);
    });

    /* ---------- Filtros ---------- */
    var filtros = document.querySelectorAll('.filtro');
    var cartas = document.querySelectorAll('.carta');

    filtros.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filtros.forEach(function (b) {
                b.classList.toggle('activo', b === btn);
                b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
            });
            var filtro = btn.dataset.filtro;
            cartas.forEach(function (carta) {
                var estado = carta.dataset.estado;
                var visible = filtro === 'todos' ||
                    (filtro === 'disponibles' && estado === 'disponible') ||
                    (filtro === 'elegidos' && estado === 'elegido');
                carta.classList.toggle('oculta', !visible);
            });
        });
    });

    /* ---------- Modal ---------- */
    var modal = document.getElementById('modal');
    if (!modal) { return; }

    var pasoForm = document.getElementById('paso-formulario');
    var pasoGracias = document.getElementById('paso-gracias');
    var form = document.getElementById('form-claim');
    var campoGiftId = document.getElementById('campo-gift-id');
    var modalRegalo = document.getElementById('modal-regalo');
    var formError = document.getElementById('form-error');
    var btnConfirmar = document.getElementById('btn-confirmar');
    var graciasMensaje = document.getElementById('gracias-mensaje');
    var elegidoConExito = false;

    function abrirModal(id, nombre) {
        elegidoConExito = false;
        campoGiftId.value = id;
        modalRegalo.textContent = nombre;
        form.reset();
        campoGiftId.value = id;
        formError.hidden = true;
        pasoForm.hidden = false;
        pasoGracias.hidden = true;
        modal.hidden = false;
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { modal.classList.add('abierto'); });
        });
        document.body.style.overflow = 'hidden';
        var primerCampo = form.querySelector('input[name="nombre"]');
        setTimeout(function () { primerCampo.focus(); }, 450);
    }

    function cerrarModal() {
        modal.classList.remove('abierto');
        document.body.style.overflow = '';
        setTimeout(function () {
            modal.hidden = true;
            if (elegidoConExito) { window.location.reload(); }
        }, 420);
    }

    document.querySelectorAll('.btn-elegir[data-id]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            abrirModal(btn.dataset.id, btn.dataset.nombre);
        });
    });

    modal.querySelectorAll('[data-cerrar]').forEach(function (el) {
        el.addEventListener('click', cerrarModal);
    });

    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && !modal.hidden) { cerrarModal(); }
    });

    /* ---------- Envío del formulario ---------- */
    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        formError.hidden = true;

        var datos = new FormData(form);
        var cedula = String(datos.get('cedula') || '').replace(/\D/g, '');

        if (String(datos.get('nombre') || '').trim().length < 3) {
            return mostrarError('Cuéntanos tu nombre completo, por favor.');
        }
        if (!/^[0-9]{10}$/.test(cedula)) {
            return mostrarError('La cédula debe tener 10 dígitos.');
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(datos.get('correo') || ''))) {
            return mostrarError('Ingresa un correo válido, por favor.');
        }
        datos.set('cedula', cedula);

        btnConfirmar.disabled = true;

        fetch('api/claim.php', { method: 'POST', body: datos })
            .then(function (res) { return res.json().then(function (json) { return { status: res.status, json: json }; }); })
            .then(function (r) {
                btnConfirmar.disabled = false;
                if (r.json.ok) {
                    elegidoConExito = true;
                    graciasMensaje.textContent = r.json.mensaje +
                        ' Para los demás invitados este regalo ya aparecerá como elegido.';
                    pasoForm.hidden = true;
                    pasoGracias.hidden = false;
                } else {
                    mostrarError(r.json.error || 'Ocurrió un error. Intenta de nuevo.');
                    if (r.status === 409) { elegidoConExito = true; } // recargar al cerrar: el estado cambió
                }
            })
            .catch(function () {
                btnConfirmar.disabled = false;
                mostrarError('No pudimos conectar. Revisa tu internet e intenta otra vez.');
            });
    });

    function mostrarError(msg) {
        formError.textContent = msg;
        formError.hidden = false;
    }
})();
