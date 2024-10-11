export default function handleObjectToFormData(obj, formData, key) {
    if (obj === undefined) {
        obj = null;
    } else if (Array.isArray(obj)) {
        for (let itemIndex in obj) {
            handleObjectToFormData(obj[itemIndex], formData, `${key}[${itemIndex}]`);
        }
    } else if (obj instanceof File) {
        formData.append(key, obj);
    } else if (obj instanceof Object && Object.keys(obj).length > 0) {
        for (let objKey in obj) {
            handleObjectToFormData(obj[objKey], formData, `${key}[${objKey}]`);
        }
    } else {
        formData.append(key, obj);
    }
}