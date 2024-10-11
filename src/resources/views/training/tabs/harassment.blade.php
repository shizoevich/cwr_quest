<div class="container-fluid" style="padding-top: 15px;">
    <div id="harassment-certificate-status"></div>

    <div class="well position-relative">
        <div id="harassment-certificate-loader" class="well-loader">
            <img src="{{ asset('images/pageloader.gif') }}" alt="Loading..." class="">
        </div>

        <h3 class="well-title">Mandatory Annual Sexual Harassment Prevention Training</h3>

        <div id="harassment-certificate-top-message">
            <p>
                In accordance with state regulations, all therapists are required to complete annual Sexual Harassment Prevention Training.
                This training is crucial for maintaining a safe and respectful work environment for everyone.
            </p>

            <p style="margin-bottom:0;">
                <b>To Complete the Training:</b>
            </p>
            <ol>
                <li>Visit the State of California Department of Civil Rights training page here: <a href="https://calcivilrights.ca.gov/shpt/" target="_blank">https://calcivilrights.ca.gov/shpt/</a>.</li>
                <li>Follow the instructions to complete the training session.</li>
                <li>After finishing, you will receive a certificate of completion.</li>
            </ol>
            
            <p>
                <b>To Submit Your Certificate:</b> <br>
                Please click 'Select file' below to upload the certificate promptly after completion.
            </p>
        </div>

        <label for="harassment_certificate" class="harassment-certificate-file-upload">
            <button
                id="choose-harassment-certificate-file"
                class="btn btn-primary"
                onclick="document.getElementById('harassment_certificate').click()"
            >
                Select file
            </button>
            <span id="harassment-certificate-file-name"></span>
        </label>
        
        <input
            type="file"
            id="harassment_certificate"
            name="harassment_certificate"
            accept=".jpg,.jpeg,.png,.pdf"
            style="display: none;"
        >

        <div id="harassment-certificate-bottom-message"></div>
    </div>
</div>

@section('scripts')
    @parent
    <script>
        let certificateUploaded = false;

        document.addEventListener('DOMContentLoaded', function() {
            checkCertificate();
        });

        function checkCertificate() {
            axios({
                method: 'get',
                url: '/user/harassment-certificate',
            })
                .then((response) => {
                    if (response && response.data && response.data.harassment_certificate_original_name) {
                        certificateUploaded = true;
                        document.getElementById('harassment-certificate-file-name').textContent = response.data.harassment_certificate_original_name;
                        successHarassmentCertificateHandler();
                    }

                    document.getElementById('harassment-certificate-loader').remove();
                })
                .catch((error) => {
                    resetStatus();
                    setDangerStatus('Oops, something went wrong!');
                });
        }

        document.getElementById('harassment_certificate').addEventListener('change', function() {
            var file = this.files[0];
            if (file) {
                var fileName = file.name;
                document.getElementById('harassment-certificate-file-name').textContent = fileName;

                uploadFile(file);
            } else {
                document.getElementById('harassment-certificate-file-name').textContent = '';
            }
        });

        function uploadFile(file) {
            let formData = new FormData();
            formData.append('harassment_certificate', file);

            axios.post('/user/harassment-certificate', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(() => {
                    certificateUploaded = true;
                    successHarassmentCertificateHandler(true);
                })
                .catch(() => {
                    document.getElementById('harassment-certificate-file-name').textContent = '';
                    errorHarassmentCertificateHandler();
                });
        }

        function successHarassmentCertificateHandler (showSuccessMessage = false) {
            resetStatus();

            if (showSuccessMessage) {
                setSuccessStatus('Your certificate successfully uploaded!');
            }
            
            document.getElementById('harassment-certificate-top-message').innerHTML = `<p>You may upload a new version of certificate if necessary.</p>`;
            document.getElementById('harassment-certificate-bottom-message').innerHTML = `
                <p>Repeat training:
                    <a href="https://calcivilrights.ca.gov/shpt/" target="_blank">https://calcivilrights.ca.gov/shpt/</a>
                </p>
            `;
        }

        function errorHarassmentCertificateHandler () {
            resetStatus();
            setDangerStatus('An error occurred while uploading the certificate.');
            
            if (certificateUploaded) {
                return;
            }
            
            document.getElementById('harassment-certificate-top-message').innerHTML = `
                <p>
                    In accordance with state regulations, all therapists are required to complete annual Sexual Harassment Prevention Training.
                    This training is crucial for maintaining a safe and respectful work environment for everyone.
                </p>

                <p style="margin-bottom:0;">
                    <b>To Complete the Training:</b>
                </p>
                <ol>
                    <li>Visit the State of California Department of Civil Rights training page here: <a href="https://calcivilrights.ca.gov/shpt/" target="_blank">https://calcivilrights.ca.gov/shpt/</a>.</li>
                    <li>Follow the instructions to complete the training session.</li>
                    <li>After finishing, you will receive a certificate of completion.</li>
                </ol>
                
                <p>
                    <b>To Submit Your Certificate:</b> <br>
                    Please click 'Select file' below to upload the certificate promptly after completion.
                </p>
            `;
            document.getElementById('harassment-certificate-bottom-message').innerHTML = '';
        }        

        function setSuccessStatus(message) {
            setStatus(message, true);
        }

        function setDangerStatus(message) {
            setStatus(message, false);
        }

        function setStatus(message, success = false) {
            const statusClass = success ? 'alert-success' : 'alert-danger';
            document.getElementById('harassment-certificate-status').innerHTML = `<p>${message}</p>`;
            document.getElementById('harassment-certificate-status').classList.add('alert', statusClass);

            setTimeout(() => {
                resetStatus();
            }, 10000);
        }

        function resetStatus() {
            document.getElementById('harassment-certificate-status').innerHTML = '';
            document.getElementById('harassment-certificate-status').classList.remove('alert');
            document.getElementById('harassment-certificate-status').classList.remove('alert-success');
            document.getElementById('harassment-certificate-status').classList.remove('alert-danger');
        }
    </script>
@endsection