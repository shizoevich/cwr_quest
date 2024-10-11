import { MAX_UPLOAD_FILE_SIZE_IN_BITES } from "../settings";

export default {
    methods: {
        validateFileSize(file) {
            const maxFileSize = MAX_UPLOAD_FILE_SIZE_IN_BITES;
            const maxFileSizeInMb = (maxFileSize / 1000000).toFixed(1);

            if (file.size > maxFileSize) {
                this.$message({
                    type: "error",
                    message: `The file is too large. Maximum file size is ${maxFileSizeInMb}MB.`,
                    duration: 10000,
                });

                return false;
            }

            return true;
        },
    },
};
