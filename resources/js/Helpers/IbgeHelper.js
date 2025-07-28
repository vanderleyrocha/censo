// resources/js/Helpers/IbgeHelper.js

export function formatarIbge(ibge) {
    if (!ibge || ibge.length !== 7) {
        return ibge; // Retorna sem formatação se não tiver 7 dígitos
    }

    const estado = ibge.substring(0, 2);
    const municipio = ibge.substring(2, 6);
    const digito = ibge.substring(6, 7);

    return `${estado}.${municipio}-${digito}`;
}