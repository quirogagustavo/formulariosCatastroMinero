/**
 * POSGAR 94 -> POSGAR 2007 Transformation
 * 
 * Métodos disponibles:
 * 
 * 1. IGN (Instituto Geográfico Nacional)
 *    - Utiliza transformación Helmert de 7 parámetros
 *    - Parámetros: dx=-11.340, dy=-6.686, dz=3.836
 *    - Rotación (arc-seconds): rx=0.214569, ry=-0.102025, rz=0.374988
 *    - Factor de escala (ppm): ds=0.121173600
 *    - Fuente: IGN Argentina - Marco de Referencia Geodésico Nacional
 * 
 * 2. GPAC (Gestión Provincial de Agrimensura y Catastro)
 *    - Utiliza fórmula de corrección local para San Juan
 *    - ESTE_07 = ESTE_94 + 1.6349059209 + (-2.016e-7 * NORTE_94)
 *    - NORTE_07 = NORTE_94 + 0.0947941194 + (2.213e-7 * ESTE_94)
 * 
 * Versión: 5.0
 * Fecha: 2025-12-01
 */

// Definir sistemas de coordenadas una sola vez
(function() {
    // POSGAR 2007 Faja 2 - Proyectado (coordenadas planas)
    // Basado en elipsoide GRS80
    proj4.defs("EPSG:5344", 
        "+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 " +
        "+ellps=GRS80 +units=m +no_defs"
    );
    
    // POSGAR 94 Faja 2 - Proyectado (coordenadas planas)
    // Basado en elipsoide WGS84 con parámetros de transformación IGN
    // towgs84: dx,dy,dz (metros), rx,ry,rz (arc-seconds), ds (ppm)
    proj4.defs("EPSG:22182", 
        "+proj=tmerc +lat_0=-90 +lon_0=-69 +k=1 +x_0=2500000 +y_0=0 " +
        "+ellps=WGS84 " +
        "+towgs84=-11.340,-6.686,3.836,0.214569,-0.102025,0.374988,0.121173600 " +
        "+units=m +no_defs"
    );
})();

/**
 * Convierte coordenadas de POSGAR 94 a POSGAR 2007
 * utilizando los parámetros oficiales del IGN
 * 
 * La transformación se realiza directamente entre sistemas proyectados:
 * POSGAR 94 Faja 2 (EPSG:22182 con towgs84 IGN) → POSGAR 2007 Faja 2 (EPSG:5344)
 * 
 * Los parámetros towgs84 del IGN están definidos en EPSG:22182, lo que permite
 * a proj4 aplicar automáticamente la transformación Helmert de 7 parámetros
 * durante la conversión entre los sistemas proyectados.
 * 
 * @param {number} este94 - Coordenada Este en POSGAR 94 Faja 2 (EPSG:22182)
 * @param {number} norte94 - Coordenada Norte en POSGAR 94 Faja 2 (EPSG:22182)
 * @returns {Object} Objeto con coordenadas convertidas: {este07, norte07, diferencias}
 */
function convertirPOSGAR94a2007_IGN(este94, norte94) {
    try {
        // Validar entrada
        if (isNaN(este94) || isNaN(norte94)) {
            throw new Error('Las coordenadas deben ser números válidos');
        }
        
        // Validar rangos razonables para San Juan
        if (este94 < 2000000 || este94 > 3000000) {
            console.warn('Coordenada ESTE fuera de rango esperado para San Juan');
        }
        if (norte94 < 6000000 || norte94 > 7000000) {
            console.warn('Coordenada NORTE fuera de rango esperado para San Juan');
        }
        
        console.log(`[POSGAR Transform IGN] Inicio: POSGAR 94 (${este94.toFixed(2)}, ${norte94.toFixed(2)})`);
        
        // Transformación directa POSGAR 94 → POSGAR 2007
        // proj4 aplica automáticamente los parámetros towgs84 definidos en EPSG:22182
        const [este07, norte07] = proj4('EPSG:22182', 'EPSG:5344', [este94, norte94]);
        console.log(`[POSGAR Transform IGN] Resultado: POSGAR 2007 (${este07.toFixed(2)}, ${norte07.toFixed(2)})`);
        
        // Calcular diferencias
        const diffEste = este07 - este94;
        const diffNorte = norte07 - norte94;
        console.log(`[POSGAR Transform IGN] Diferencias: ΔE=${diffEste.toFixed(3)}m, ΔN=${diffNorte.toFixed(3)}m`);
        
        return {
            este07: este07,
            norte07: norte07,
            diferencias: {
                este: Math.abs(diffEste),
                norte: Math.abs(diffNorte)
            }
        };
        
    } catch (error) {
        console.error('[POSGAR Transform IGN] Error en conversión:', error);
        throw error;
    }
}

/**
 * Convierte coordenadas de POSGAR 94 a POSGAR 2007
 * utilizando los parámetros de transformación GPAC (Gestión Provincial de Agrimensura y Catastro)
 * 
 * Fórmula GPAC:
 * - ESTE_07 = ESTE_94 + 1.6349059209 + (-2.016e-7 * NORTE_94)
 * - NORTE_07 = NORTE_94 + 0.0947941194 + (2.213e-7 * ESTE_94)
 * 
 * @param {number} este94 - Coordenada Este en POSGAR 94 Faja 2
 * @param {number} norte94 - Coordenada Norte en POSGAR 94 Faja 2
 * @returns {Object} Objeto con coordenadas convertidas: {este07, norte07, diferencias}
 */
function convertirPOSGAR94a2007_GPAC(este94, norte94) {
    try {
        // Validar entrada
        if (isNaN(este94) || isNaN(norte94)) {
            throw new Error('Las coordenadas deben ser números válidos');
        }
        
        // Validar rangos razonables para San Juan
        if (este94 < 2000000 || este94 > 3000000) {
            console.warn('Coordenada ESTE fuera de rango esperado para San Juan');
        }
        if (norte94 < 6000000 || norte94 > 7000000) {
            console.warn('Coordenada NORTE fuera de rango esperado para San Juan');
        }
        
        console.log(`[POSGAR Transform GPAC] Inicio: POSGAR 94 (${este94.toFixed(2)}, ${norte94.toFixed(2)})`);
        
        // Aplicar fórmula GPAC
        const este07 = este94 + 1.6349059209 + (-2.016e-7 * norte94);
        const norte07 = norte94 + 0.0947941194 + (2.213e-7 * este94);
        
        console.log(`[POSGAR Transform GPAC] Resultado: POSGAR 2007 (${este07.toFixed(2)}, ${norte07.toFixed(2)})`);
        
        // Calcular diferencias
        const diffEste = este07 - este94;
        const diffNorte = norte07 - norte94;
        console.log(`[POSGAR Transform GPAC] Diferencias: ΔE=${diffEste.toFixed(3)}m, ΔN=${diffNorte.toFixed(3)}m`);
        
        return {
            este07: este07,
            norte07: norte07,
            diferencias: {
                este: Math.abs(diffEste),
                norte: Math.abs(diffNorte)
            }
        };
        
    } catch (error) {
        console.error('[POSGAR Transform GPAC] Error en conversión:', error);
        throw error;
    }
}

/**
 * Función principal de conversión que permite elegir el método
 * 
 * @param {number} este94 - Coordenada Este en POSGAR 94 Faja 2
 * @param {number} norte94 - Coordenada Norte en POSGAR 94 Faja 2
 * @param {string} metodo - Método de transformación: 'IGN' o 'GPAC' (por defecto 'IGN')
 * @returns {Object} Objeto con coordenadas convertidas: {este07, norte07, diferencias, metodo}
 */
function convertirPOSGAR94a2007(este94, norte94, metodo = 'GPAC') {
    metodo = metodo.toUpperCase();
    
    if (metodo === 'IGN') {
        const resultado = convertirPOSGAR94a2007_IGN(este94, norte94);
        resultado.metodo = 'IGN';
        return resultado;
    } else {
        const resultado = convertirPOSGAR94a2007_GPAC(este94, norte94);
        resultado.metodo = 'GPAC';
        return resultado;
    }
}

/**
 * Valida que las coordenadas estén en rangos válidos para POSGAR 94 Faja 2
 * 
 * @param {number} este - Coordenada Este
 * @param {number} norte - Coordenada Norte
 * @returns {Object} {valido: boolean, mensaje: string}
 */
function validarCoordenadasPOSGAR94(este, norte) {
    // Validar que sean números
    if (isNaN(este) || isNaN(norte)) {
        return {
            valido: false,
            mensaje: 'Las coordenadas deben ser números válidos'
        };
    }
    
    // Validar rango ESTE (Faja 2)
    if (este < 2000000 || este >= 3000000) {
        return {
            valido: false,
            mensaje: '⚠️ ERROR: La coordenada X (ESTE) debe comenzar con 2\nEjemplo: 2513614.40'
        };
    }
    
    // Validar rango NORTE (San Juan)
    if (norte < 6000000 || norte >= 7000000) {
        return {
            valido: false,
            mensaje: '⚠️ ERROR: La coordenada Y (NORTE) debe comenzar con 6\nEjemplo: 6596573.50'
        };
    }
    
    return {
        valido: true,
        mensaje: 'Coordenadas válidas'
    };
}

/**
 * Valida que las coordenadas estén en rangos válidos para POSGAR 2007 Faja 2
 * 
 * @param {number} este - Coordenada Este
 * @param {number} norte - Coordenada Norte
 * @returns {Object} {valido: boolean, mensaje: string}
 */
function validarCoordenadasPOSGAR2007(este, norte) {
    // Validar que sean números
    if (isNaN(este) || isNaN(norte)) {
        return {
            valido: false,
            mensaje: 'Las coordenadas deben ser números válidos'
        };
    }
    
    // Validar rango ESTE (Faja 2)
    if (este < 2000000 || este >= 3000000) {
        return {
            valido: false,
            mensaje: '⚠️ ERROR: La coordenada X (ESTE) debe comenzar con 2\nEjemplo: 2513614.71'
        };
    }
    
    // Validar rango NORTE (San Juan)
    if (norte < 6000000 || norte >= 7000000) {
        return {
            valido: false,
            mensaje: '⚠️ ERROR: La coordenada Y (NORTE) debe comenzar con 6\nEjemplo: 6596570.58'
        };
    }
    
    return {
        valido: true,
        mensaje: 'Coordenadas válidas'
    };
}
