export function truncate(str, maxlength) {
    return (str && str.length > maxlength) ? str.slice(0, maxlength - 1).trim() + '...' : str;
}
