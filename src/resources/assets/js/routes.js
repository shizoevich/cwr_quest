import * as Chart from './components/Chart';
import * as PageNotFound from './components/PageNotFound';
import * as MissingNotesStatistic from './components/dashboard/MissingNotesStatistic';
import * as PatientsStatistic from './components/dashboard/PatientsStatistic';
import * as PatientsWithoutAppointmentsStatistic from './components/dashboard/PatientsWithoutAppointmentsStatistic';
import * as MainView from './components/MainView';
import * as Calendar from './components/Calendar';
import * as UpcomingReauthorizationRequests from './components/dashboard/UpcomingReauthorizationRequests';
import * as PatientsAssignedToTherapistsStatistic from './components/dashboard/PatientsAssignedToTherapistsStatistic';
import * as TotalVcAndPnStatistic from './components/dashboard/TotalVcAndPnStatistic';
import * as Dashboard from './components/dashboard/Dashboard';
import * as DocumentsToSend from './components/dashboard/DocumentsToSend';
import * as Request from './components/dashboard/Request';
import * as KaiserAppointments from './components/dashboard/KaiserAppointments';
import * as PastAppointments from './components/dashboard/PastAppointments';
import Parsers from "./components/dashboard/Parsers";
// import * as CompletedAppointments from './components/dashboard/CompletedAppointments';
import * as TimeRecords from './components/salary/time-records/TimeRecords';
import TimeRecordsThanksPage from "./components/salary/time-records/ThanksPage";
import TimesheetsPage from "./components/timesheets/index";
import Timesheets from "./components/timesheets/Timesheets";
import Timesheet from "./components/timesheets/single/Timesheet";
import CheckChargeForCancellation from './components/CheckChargeForCancellation'
import SalaryQuotaCalculator from "./components/salary/SalaryQuotaCalculator.vue";


const routes = [
    {
        path: '/chart',
        component: Chart,
        name: 'chart',
        children: [
            { path: 'calendar', component: Calendar },
            { path: ':id', component: MainView, name: 'patient-chart' },
            { path: '', component: Dashboard }
        ]
    },

    {
        path: '/dashboard/timesheets',
        component: TimesheetsPage,
        name: 'timesheets',
        children: [
            { path: ':id', name: 'timesheet', component: Timesheet },
            { path: '', component: Timesheets }
        ]
    },

    {
        path: '/salary/time-records',
        component: TimeRecords
    },

    {
        path: '/statistic/missing-notes',
        component: MissingNotesStatistic
    },

    {
        path: '/statistic/patients',
        component: PatientsStatistic
    },

    {
        path: '/dashboard/statistic/patients-without-appointments',
        component: PatientsWithoutAppointmentsStatistic
    },

    {
        path: '/dashboard/parsers',
        component: Parsers
    },

    {
        path: '/statistic/upcoming-reauthorization-requests',
        component: UpcomingReauthorizationRequests
    },

    {
        path: '/statistic/patients-assigned-to-therapists',
        component: PatientsAssignedToTherapistsStatistic
    },

    {
        path: '/dashboard/statistic/total-vc-and-pn',
        component: TotalVcAndPnStatistic
    },

    {
        path: '/dashboard/documents-to-send',
        component: DocumentsToSend
    },

    {
        path: '/past-appointments',
        component: PastAppointments,
    },

    // {
    //     path: '/dashboard/appointments/completed',
    //     component: CompletedAppointments
    // },

    {
        path: '/dashboard/request',
        component: Request
    },

    {
        path: '/salary/time-records/thanks',
        component: TimeRecordsThanksPage
    },

    {
        path: '/check-charge-for-cancellation',
        component: CheckChargeForCancellation
    },

    {
        path: '/salary-quota-calculator',
        component: SalaryQuotaCalculator,
    },

    {
        path: '*',
        component: PageNotFound,
        name: '404'
    }
];
export default routes;