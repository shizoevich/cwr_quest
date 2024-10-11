export default function handlerServerError(error, self) {
    if (error.response && error.response.data && error.response.status === 422) {
        handleErrorMessage(error.response.data, self)
        return;
    }
    self.$message({
        type: 'error',
        message: 'Oops, something went wrong!',
        duration: 10000,
    });
}

function handleErrorMessage(errors, self) {
    for (const errorsName in errors) {
        if (errors.hasOwnProperty(errorsName)) {
            errors[errorsName].forEach(error => {
                setTimeout(() => {
                    self.$message({
                        type: 'error',
                        message: error,
                        duration: 10000,
                    });
                }, 300)
            })
        }
    }
}