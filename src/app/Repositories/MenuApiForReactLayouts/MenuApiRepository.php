<?php

namespace App\Repositories\MenuApiForReactLayouts;

use App\User;
use App\KaiserAppointment;
use App\Models\Patient\PatientNoteUnlockRequest;
use App\Models\Patient\PatientRemovalRequest;
use Illuminate\Support\Facades\Auth;

class MenuApiRepository implements MenuApiRepositoryInterface
{
    public function getMenuData(): array
    {
        $user = User::with('roles')->find(Auth::user()->id);

        if (isset($user)) {
            if ($user->isOnlyProvider()) {
                return $this->getProviderLinks($user);
            }

            if ($user->isPatientRelationManager()) {
                return $this->getPatientRelationManagerLinks($user);
            }
            
            if ($user->isAdmin()) {
                return $this->getAdminSecretaryLinks($user);
            }
        }

        return ['links' => null, 'user_links' => null, 'user_name' => null];
    }

    private function getProviderLinks($user)
    {
        $userLinks = [
            [
                'name' => 'Profile', 
                'link' => '/profile', 
                'img' => [
                    'url' => '/images/icons/profile-icon.svg',
                    'alt' => 'profile-icon',
                ]
            ],
            [
                'name' => 'Logout',
                'link' => '/logout',
                'img' => [
                    'url' => '/images/icons/logout-icon.svg',
                    'alt' => 'logout-icon',
                ]
            ]
        ];

        $moreSubMenu = [
            ['name' => 'Lucet', 'link' => 'https://polestarapp.com/admin/login'],
            ['name' => 'Timesheet', 'link' => '/salary/time-records'],
            ['name' => 'Notifications', 'link' => '/update-notifications/history'],
            ['name' => 'Training', 'link' => '/user/training'],
        ];

        if ($user->isSupervisor()) {
            $moreSubMenu[] = ['name' => 'Supervision', 'link' => '/supervisor-dashboard'];
        }

        $links = [
            ['name' => 'Home', 'link' => '/chart'],
            ['name' => 'Patients', 'link' => '/statistic/patients-assigned-to-therapists'],
            ['name' => 'Scheduling', 'link' => '/chart/calendar'],
            ['name' => 'More', 'submenu' => $moreSubMenu],
        ];

        return ['links' => $links, 'user_links' => $userLinks, 'user_name' => $user->getFullName()];
    }

    private function getPatientRelationManagerLinks($user)
    {
        $userLinks = [
            [
                'name' => 'Change Password',
                'link' => '/change-password',
                'img' => [
                    'url' => '/images/icons/change-password-icon.svg',
                    'alt' => 'change-password-icon',
                ]
            ],
            [
                'name' => 'Logout',
                'link' => '/logout',
                'img' => [
                    'url' => '/images/icons/logout-icon.svg',
                    'alt' => 'logout-icon',
                ]
            ]
        ];

        $links = [
            ['name' => 'Home', 'link' => '/chart'],
            ['name' => 'Appointments', 'link' => '/dashboard/appointments/ehr'],
            [
                'name' => 'Statistics', 'submenu' => [
                    ['name' => 'Missing Progress Notes', 'link' => '/statistic/missing-notes'],
                    ['name' => 'Missing upcoming appointments', 'link' => '/statistic/patients'],
                    ['name' => 'Patients with no appointments', 'link' => '/dashboard/statistic/patients-without-appointments'],
                    ['name' => 'Sent Documents', 'link' => '/dashboard/statistic/sent-documents'],
                    ['name' => 'Therapists availability', 'link' => '/dashboard/statistic/therapists-availability'],
                    ['name' => 'Assigned patients', 'link' => '/statistic/patients-assigned-to-therapists'],
                    ['name' => 'Reauthorization Requests', 'link' => '/statistic/upcoming-reauthorization-requests'],
                ]
            ]
        ];

        $moreSubMenu = [
            ['name' => 'Faxes', 'link' => '/faxes'],
            ['name' => 'Secretary Dashboard', 'link' => '/secretary-dashboard'],
            ['name' => 'New Patients Dashboard', 'link' => '/new-patients-dashboard'],
            ['name' => 'Notifications', 'link' => '/update-notifications/history'],
            ['name' => 'Training', 'link' => '/user/training']
        ];

        $links[] = ['name' => 'More', 'submenu' => $moreSubMenu];

        return ['links' => $links, 'user_links' => $userLinks, 'user_name' => $user->getFullName()];
    }

    private function getAdminSecretaryLinks($user)
    {
        $patientRemovalRequestCount = PatientRemovalRequest::new()->count();
        $patientNoteUnlockRequestCount = PatientNoteUnlockRequest::new()->count();

        $userLinks = [
            [
                'name' => 'Change Password',
                'link' => '/change-password',
                'img' => [
                    'url' => '/images/icons/change-password-icon.svg',
                    'alt' => 'change-password-icon',
                ]
            ],
            [
                'name' => 'Logout',
                'link' => '/logout',
                'img' => [
                    'url' => '/images/icons/logout-icon.svg',
                    'alt' => 'logout-icon',
                ]
            ]
        ];

        $links = [
            ['name' => 'Home', 'link' => '/chart'],
            ['name' => 'Users', 'link' => '/dashboard/doctors'],
            ['name' => 'Providers', 'link' => '/dashboard/providers'],
            [
                'name' => 'Pending Requests', 'items_count' => $patientRemovalRequestCount + $patientNoteUnlockRequestCount,
                'submenu' => [
                    ['name' => 'Removal Requests', 'link' => '/dashboard/patient-removal-requests', 'items_count' => $patientRemovalRequestCount],
                    ['name' => 'Progress Note Unlock Requests', 'link' => '/dashboard/patient-note-unlock-requests', 'items_count' => $patientNoteUnlockRequestCount],
                ]
            ],
            ['name' => 'Appointments', 'link' => '/dashboard/appointments/ehr'],
            ['name' => 'Availability / Scheduling', 'link' => '/dashboard/doctors-availability']
        ];

        if ($user->isOnlyAdmin()) {
            $links[] = ['name' => 'Square Customers', 'link' => '/dashboard/square/customers/unattached'];
            $links[] = [
                'name' => 'Accounting', 'submenu' => [
                    ['name' => 'Fee Schedule', 'link' => '/dashboard/tariffs-plans'],
                    ['name' => 'Timesheets', 'link' => '/dashboard/timesheets'],
                    ['name' => 'Service Payouts', 'link' => '/dashboard/salary'],
                    ['name' => 'Appointments', 'link' => '/dashboard/appointments/completed'],
                    ['name' => 'Posting', 'link' => '/dashboard/appointments/completed?tab=posting-tab'],
                ]
            ];
        }

        $links[] = [
            'name' => 'Statistics', 'submenu' => [
                ['name' => 'Missing Progress Notes', 'link' => '/statistic/missing-notes'],
                ['name' => 'Missing upcoming appointments', 'link' => '/statistic/patients'],
                ['name' => 'Patients with no appointments', 'link' => '/dashboard/statistic/patients-without-appointments'],
                ['name' => 'Sent Documents', 'link' => '/dashboard/statistic/sent-documents'],
                ['name' => 'Therapists availability', 'link' => '/dashboard/statistic/therapists-availability'],
                ['name' => 'Assigned patients', 'link' => '/statistic/patients-assigned-to-therapists'],
                ['name' => 'Reauthorization Requests', 'link' => '/statistic/upcoming-reauthorization-requests'],
            ]
        ];
        $links[] = ['name' => 'Lucet', 'link' => 'https://polestarapp.com/admin/login'];
        $links[] = ['name' => 'Sync with OA', 'link' => '/dashboard/parsers'];

        $moreSubMenu = [
            ['name' => 'Faxes', 'link' => '/faxes'],
            ['name' => 'Secretary Dashboard', 'link' => '/secretary-dashboard'],
            ['name' => 'New Patients Dashboard', 'link' => '/new-patients-dashboard'],
            ['name' => 'Patients Management Dashboard', 'link' => '/dashboard/patients-management'],
            ['name' => 'Reauthorization Requests Dashboard', 'link' => '/dashboard/reauthorization-request']
        ];

        if ($user->isOnlyAdmin()) {
            $moreSubMenu[] = ['name' => 'Doctors Requests Dashboard', 'link' => '/dashboard/doctors-requests'];
        }

        if ($user->isOnlyAdmin()) {
            $moreSubMenu[] = ['name' => 'Notifications', 'link' => '/update-notifications'];
            $moreSubMenu[] = ['name' => 'Notification Templates', 'link' => '/update-notification-templates'];
            $moreSubMenu[] = ['name' => 'Supervision', 'link' => '/supervisor-dashboard'];
            $moreSubMenu[] = ['name' => 'Salary Quota Calculator', 'link' => '/salary-quota-calculator'];
        } else {
            $moreSubMenu[] = ['name' => 'Notifications', 'link' => '/update-notifications/history'];
        }

        if ($user->isSecretary()) {
            $moreSubMenu[] = ['name' => 'Training', 'link' => '/user/training'];
        }

        $links[] = ['name' => 'More', 'submenu' => $moreSubMenu];

        return ['links' => $links, 'user_links' => $userLinks, 'user_name' => $user->getFullName()];
    }
}
