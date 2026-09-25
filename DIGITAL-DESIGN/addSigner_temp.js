function addSigner() {
    const id = signerCounter++;
    const list = document.getElementById('signerList');
    document.getElementById('no-signers').classList.add('hidden');

    const signer = { id: id, pad: null };
    signers.push(signer);

    const card = document.createElement('div');
    card.className = "signer-card p-6 animate-fade-in";
    card.id = `signer-${id}`;
    card.innerHTML = `
                <div class="flex justify-between items-start mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-900 text-white rounded-full flex items-center justify-center text-sm font-bold">${signers.length}</div>
                        <div>
                            <h4 class="font-bold text-slate-800">Firmante </h4>
                            <p class="text-xs text-slate-500">Personaliza la firma y certificado</p>
                        </div>
                    </div>
                    <button onclick="removeSigner(${id})" class="text-slate-400 hover:text-red-500 transition-colors p-2">✕</button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nombre del Firmante</label>
                        <input type="text" id="name-${id}" oninput="checkFormValidity()" placeholder="Ej: Lic. Antonio Solis" class="w-full border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all"/>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Certificado XMLDSig</label>
                        <select id="cert-${id}" onchange="checkFormValidity()" class="w-full border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all bg-white font-medium">
                            <option value="">(Sin firma digital avanzada)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Trazo de Firma Manuscrita</label>
                        <button onclick="clearPad(${id})" class="text-[10px] font-bold text-red-500 uppercase hover:underline">Limpiar Panel</button>
                    </div>
                    <canvas id="canvas-${id}" class="sig-canvas w-full h-[160px] shadow-sm touch-none"></canvas>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-slate-50 rounded-2xl">
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Ubicación Visual de la Firma</label>
                        <select id="placement-${id}" onchange="updatePlacementUI(${id})" class="w-full text-sm border-none bg-transparent font-bold text-slate-700 outline-none focus:ring-0">
                            <option value="end">Página Final</option>
                            <option value="paragraph">Párrafo Nº</option>
                            <option value="keyword">Palabra Clave</option>
                        </select>
                    </div>
                    <div id="placement-val-container-${id}" class="col-span-2 hidden animate-fade-in">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2" id="placement-label-${id}">Valor de Referencia</label>
                        <div id="keyword-select-wrapper-${id}" style="display: none;">
                            <select id="placement-keyword-${id}" class="w-full text-sm border-none bg-white p-2 rounded-lg font-bold text-slate-700 outline-none focus:ring-1 focus:ring-blue-300 transition-all shadow-inner cursor-pointer appearance-none">
                                <option value="EL TRABAJADOR">EL TRABAJADOR</option>
                                <option value="EL EMPLEADOR">EL EMPLEADOR</option>
                            </select>
                        </div>
                        <input type="text" id="placement-value-${id}" placeholder="..." class="w-full text-sm border-none bg-white p-2 rounded-lg font-bold text-slate-700 outline-none focus:ring-1 focus:ring-blue-300 transition-all shadow-inner"/>
                    </div>
                </div>
            `;

    list.appendChild(card);

    // Init Signature Pad con máxima fiabilidad
    setTimeout(() => {
        const canvas = document.getElementById(`canvas-${id}`);
        if (!canvas) return;

        const pad = new SignaturePad(canvas, {
            penColor: 'rgb(0, 0, 0)',
            backgroundColor: 'rgb(255, 255, 255)',
            minWidth: 1.5,
            maxWidth: 4.0
        });

        // Función de auto-ajuste para evitar desplazamientos de puntero
        const syncSize = () => {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const rect = canvas.getBoundingClientRect();
            if (rect.width === 0) return;

            // Solo redimensionar si las dimensiones físicas han cambiado
            if (canvas.width !== rect.width * ratio) {
                const data = pad.toData(); // Guardar trazos si ya existen
                canvas.width = rect.width * ratio;
                canvas.height = rect.height * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                pad.clear();
                pad.fromData(data); // Restaurar trazos
            }
        };

        syncSize();

        // Asegurar que al empezar a firmar la resolución esté perfecta
        pad.addEventListener("beginStroke", syncSize, { once: true });

        // Compatibilidad con validación de formulario
        pad.onEnd = () => {
            checkFormValidity();
        };

        signer.pad = pad;

        // Listener global para rotación de pantalla o redimensionado
        window.addEventListener("resize", syncSize);
    }, 500); // 500ms para asegurar que la animación de la tarjeta terminó

    // Populate Certs
    populateSignerCerts(id);
    updateGlobalStats();
}
