import * as PatientNotFound from './components/forms/PatientNotFound.vue';
import * as ChoosePatient from './components/forms/ChoosePatient.vue';
import * as ChooseForm from './components/forms/ChooseForm.vue';
import * as FormFirst from './components/forms/FormFirst.vue';
import * as FormSecond from './components/forms/FormSecond.vue';
import * as FormThird from './components/forms/FormThird.vue';
import * as AddCreditCard from './components/forms/AddCreditCard.vue';
import * as PatientNotFoundPatient from './components/forms/patients/PatientNotFound.vue';
import * as StartForm from './components/forms/StartForm';
import * as StepForm from './components/forms/StepForm';
import * as DownloadDocuments from './components/forms/DownloadDocuments';

const routes = [
    {
        path     : '/forms',
        component: ChoosePatient
    },

    {
        path     : '/forms/patient-:id',
        component: ChooseForm
    },

    {
        path     : '/forms/patient-:id/form1',
        component: FormFirst
    },

    {
        path     : '/forms/patient-:id/form2',
        component: FormSecond
    },
    {
        path     : '/forms/patient-:id/form3',
        component: FormThird
    },
    {
        path     : '/forms/patient-:id/add-credit-card',
        name     : '404',
        component: AddCreditCard
    },
    {
        path     : '/forms/404',
        name     : '404',
        component: PatientNotFound
    },
    {
        path     : '/f/:hash',
        component: StartForm
    },
    {
        path     : '/ff/:hash',
        name     : 'secure-download-forms',
        component: DownloadDocuments
    },
    {
        path     : '/f/:hash/forms',
        component: StepForm
    },
    {
        path     : '/f/:hash/download',
        name     : 'download-forms',
        component: DownloadDocuments
    },
    {
        path     : '/patient-forms/404',
        name     : 'not_found',
        component: PatientNotFoundPatient
    },
    {
        path     : '*',
        component: PatientNotFound
    }
];
export default routes;