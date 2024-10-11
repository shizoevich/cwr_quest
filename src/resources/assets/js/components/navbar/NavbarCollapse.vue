<template>
    <div :class="{ 'navbar-collapse': true, 'show': on }" ref="navbarCollapse">
        <ul>
            <template v-for="link in menuLinks.links">
                <li v-if="link.link" :key="link.name">
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

        <hr />

        <div>
            <ul>
                <li class="user-name">
                   {{ menuLinks.user_name }}
                </li>
                <li v-for="link in menuLinks.user_links" :key="link.name">
                    <a v-if="link.link === '/logout'" href="#" @click.prevent="logout">
                        <img :src="link.img.url" :alt="link.img.alt" class="icon logout-icon">
                        {{ link.name }}
                    </a>
                    <a v-else :href="link.link">
                        <img :src="link.img.url" :alt="link.img.alt" class="icon">
                        {{ link.name }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import NavbarDropdown from './NavbarDropdown';

export default {
    components: { NavbarDropdown },

    props: {
        menuLinks: {
            type: Object,
            required: true,
        },

        on: {
            type: Boolean,
            required: true,
        },

        setRef: {
            type: Function,
            required: true,
        },

        logout: {
            type: Function,
            required: true,
        }
    },

    mounted() {
        this.setRef(this.$refs.navbarCollapse);
    },
}
</script>

<style lang="scss">
.navbar {
    .navbar-collapse {
        max-height: 0;
        width: 100%;
        padding: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;

        .user-name {
            display: block;
            width: 100%;
            font-weight: bold;
            line-height: 22px;
            padding: 10px 15px;
        }

        .dropdown {
            width: 100%;

            ul {
                margin-left: 10px;
            }

            li {
                float: none !important;

                a {
                    cursor: pointer;
                }
            }

            .caret {
                transition: transform 0.3;
            }

            .dropdown-menu {
                position: static;
                float: none;
                max-height: 0;
                display: block;
                overflow: hidden;
                transition: max-height 0.1s;

                li:hover {
                    background: none;
                }

                a:hover {
                    background: none;
                }
            }
        }

        .dropdown.show {
            .dropdown-menu {
                box-shadow: none;
                border: none;
            }
        }

        hr {
            width: 100%;
            margin: 0;
            border-top: 1px solid #ddd;

            &:not(:last-child) {
                margin-bottom: 11px;
            }
        }

        .icon {
            width: 14px;
            height: auto;
            margin-right: 5px;
        }
    }

    .navbar-collapse.show {
        max-height: 340px;
        overflow: auto;
        border-top: 1px solid #e7e7e7;
    }
}
</style>