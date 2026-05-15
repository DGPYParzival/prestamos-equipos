// ==========================================================================
// FUNCIONES ADICIONALES REQUERIDAS POR LAS VISTAS
// ==========================================================================

/**
 * Confirmación antes de acciones irreversibles.
 * Uso en HTML: <a href="..." onclick="return confirmar('¿Dar de baja?')">
 *
 * @param  {string}  mensaje Pregunta a mostrar
 * @return {boolean}         true si confirma, false si cancela
 */
function confirmar(mensaje) {
    return window.confirm(mensaje);
}

/**
 * Deshabilita un botón y muestra texto de carga (cumple RNF05).
 * Evita doble clic en operaciones que tardan.
 *
 * @param {HTMLButtonElement} btn   Botón a deshabilitar
 * @param {string}            texto Texto mientras procesa
 */
function btnCargando(btn, texto = 'Procesando...') {
    btn.disabled = true;
    btn.dataset.textoOriginal = btn.textContent;
    btn.textContent = texto;
}

/**
 * Reactiva un botón después de que la operación terminó.
 *
 * @param {HTMLButtonElement} btn Botón a reactivar
 */
function btnRestaurar(btn) {
    btn.disabled = false;
    btn.textContent = btn.dataset.textoOriginal ?? 'Enviar';
}

/**
 * POST con JSON al servidor — centraliza el manejo de errores de red.
 * Todas las operaciones Fetch del sistema usan esta función.
 *
 * @param  {string} url   URL destino (ej: 'index.php?action=prestamo_aprobar')
 * @param  {object} datos Datos a enviar como JSON
 * @return {Promise<object>} Respuesta del servidor como objeto JS
 */
async function postJSON(url, datos) {
    const respuesta = await fetch(url, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(datos),
    });

    if (!respuesta.ok) {
        throw new Error(`Error del servidor: ${respuesta.status}`);
    }

    return await respuesta.json();
}