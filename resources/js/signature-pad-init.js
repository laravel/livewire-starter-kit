import SignaturePad from 'signature_pad';

window.initializeSignaturePad = function(component) {
    let signaturePad = null;
    
    function initSignaturePad() {
        const canvas = document.getElementById('signature-canvas');
        if (!canvas) return;
        
        // Set canvas size
        canvas.width = canvas.offsetWidth;
        canvas.height = 200;
        
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });
        
        // Listen for signature changes
        signaturePad.addEventListener('endStroke', () => {
            if (!signaturePad.isEmpty()) {
                const dataURL = signaturePad.toDataURL('image/png');
                component.set('signatureData', dataURL);
                component.set('useSavedSignature', false);
            }
        });
    }
    
    // Initialize when modal opens
    component.on('signature-modal-opened', () => {
        setTimeout(initSignaturePad, 100);
    });
    
    // Clear signature pad
    component.on('clear-signature-pad', () => {
        if (signaturePad) {
            signaturePad.clear();
            component.set('signatureData', null);
        }
    });
    
    // Use saved signature
    component.on('use-saved-signature', (event) => {
        if (signaturePad) {
            signaturePad.clear();
        }
    });
    
    // Watch for modal visibility
    const observer = new MutationObserver(() => {
        const canvas = document.getElementById('signature-canvas');
        if (canvas && !signaturePad) {
            initSignaturePad();
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
};
