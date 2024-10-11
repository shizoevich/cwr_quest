<template>
    <nav class="navbar" id="navbar-header">
        <div class="navbar-header">
            <template v-if="menuLinks">
                <burger-button class="burger-container" v-if="isMobileScreen" :click="toggleMenu" :on="isMenuVisible"
                    :setRef="setBurgerButtonRef" />
                <navbar-dropdown v-else :link="userLink" :isUserDropdown="true" :logout="handleLogout" />
            </template>

            <a class="navbar-brand" href="/">{{ appName }}</a>

            <template v-if="menuLinks && !isMobileScreen">
                <ul>
                    <template v-for="link in menuLinks.links">
                        <li v-if="link.link" :key="link.name" style="float: left; padding: 4px 0;">
                            <a :href="link.link">
                                {{ link.name }}
                                <span v-if="link.items_count" class="text-red">
                                    ({{ link.items_count }})
                                </span>
                            </a>
                        </li>
                        <navbar-dropdown v-else :link="link" :key="`${link.name}_dropdown`" />
                    </template>
                </ul>
            </template>
        </div>

        <navbar-collapse
            v-if="menuLinks && isMobileScreen"
            :on="isMenuVisible"
            :menu-links="menuLinks"
            :setRef="setNavbarCollapseRef"
            :logout="handleLogout"
        />

        <form id="logout-form" action="/logout" method="POST" hidden />
    </nav>
</template>

<script>
import {ADMIN_BREAKPOINT_WIDTH, PROVIDER_BREAKPOINT_WIDTH} from '../../settings'
import BurgerButton from './BurgerButton';
import NavbarCollapse from './NavbarCollapse';
import NavbarDropdown from './NavbarDropdown';

export default {
    components: { BurgerButton, NavbarCollapse, NavbarDropdown },
    props: {
        appName: {
            type: String,
            required: true,
        },
        menuLinks: {
            type: Object,
            default: null
        },
        isAdmin: {
            type: Boolean,
            required: true,
        }
    },

    computed: {
        isMobileScreen() {
            const breakpoint = this.isAdmin ? ADMIN_BREAKPOINT_WIDTH : PROVIDER_BREAKPOINT_WIDTH;
            return this.innerWidth <= breakpoint;
        },

        userLink() {
            const { user_links, user_name } = this.menuLinks;

            return { name: user_name, submenu: user_links };
        }
    },

    data() {
        return {
            isMenuVisible: false,
            innerWidth: window.innerWidth,
            burgerButtonRef: null,
            navbarCollapseRef: null,
        }
    },

    mounted() {
        window.addEventListener('resize', this.handleWindowResize);
    },

    beforeDestroy() {
        window.removeEventListener('resize', this.handleWindowResize);
    },

    methods: {
        handleWindowResize() {
            this.innerWidth = window.innerWidth;
        },

        toggleMenu() {
            this.isMenuVisible = !this.isMenuVisible;

            if (this.isMenuVisible) {
                document.addEventListener("click", this.closeMenuOnClickOutside);
            } else {
                document.removeEventListener("click", this.closeMenuOnClickOutside);
            }
        },

        setBurgerButtonRef(ref) {
            this.burgerButtonRef = ref;
        },

        setNavbarCollapseRef(ref) {
            this.navbarCollapseRef = ref;
        },

        closeMenuOnClickOutside(e) {
            const navbarCollapse = this.navbarCollapseRef;
            const burgerButton = this.burgerButtonRef;

            if (navbarCollapse && burgerButton && !navbarCollapse.contains(e.target) && !burgerButton.contains(e.target)) {
                this.isMenuVisible = false;
                this.closeAllDropdowns();
                document.removeEventListener("click", this.closeMenuOnClickOutside);
            }
        },

        closeAllDropdowns() {
            const dropdowns = document.querySelectorAll('.dropdown.show');
            dropdowns.forEach((dropdown) => {
                dropdown.classList.remove('show');
            });
        },

        handleLogout() {
            $('#logout-form').submit();
        }
    },
}
</script>

<style lang="scss">
.navbar {
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
    color: rgb(119, 119, 119);
    border-radius: 0;
    border-bottom: 1px solid #e7e7e7;

    a {
        text-decoration: none;
        color: inherit !important;
        padding: 10px 15px;
        width: 100%;
        display: block;
        line-height: 22px;

        &:hover {
            color: rgb(38, 38, 38) !important;
        }
    }

    ul {
        list-style-type: none !important;
        padding: 0;
    }

    .navbar-header {
        width: 100%;
    }

    .burger-container {
        float: right;
        margin: 8px 15px;
    }

    .navbar-brand {
        width: auto;
        line-height: 1.5;
    }
}
</style>