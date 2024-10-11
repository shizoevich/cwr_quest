<template>
    <ul :ref="id" :id="id" :class="{ 'dropdown': true, 'user-dropdown': isUserDropdown }">
        <li>
            <a href="#" class="dropdown-link" @click.prevent="toggleDropdown">
                <span class="dropdown-link-name">{{ link.name }}</span>
                <span v-if="link.items_count" class="text-red">
                    ({{ link.items_count }})
                </span>
                <span class="caret"></span>
            </a>
            <ul :class="{ 'dropdown-menu': true, 'user-dropdown-menu': isUserDropdown }">
                <li v-for="sublink in link.submenu" :key="sublink.name">
                    <a v-if="sublink.link === '/logout'" href="#" @click.prevent="logout">
                        <img :src="sublink.img.url" :alt="sublink.img.alt" class="icon logout-icon">
                        {{ sublink.name }}
                    </a>
                    <a v-else :href="sublink.link">
                        <img v-if="sublink.img" :src="sublink.img.url" :alt="sublink.img.alt" class="icon">
                        {{ sublink.name }}
                        <span v-if="sublink.items_count" class="text-red">
                            ({{ sublink.items_count }})
                        </span>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</template>

<script>
export default {
    props: {
        link: {
            type: Object,
            required: true,
        },
        isUserDropdown: {
            type: Boolean,
            default: false,
        },
        logout: {
            type: Function,
            default: null,
        }
    },

    computed: {
        id() {
            return this.getIdFromLinkName(this.link.name);
        },
    },

    methods: {
        getIdFromLinkName(name) {
            return name.replace(/\s+/g, '_').replace(/\,/g, '');
        },

        scrollIntoViewAfterTransition(dropdown) {
            const promise = new Promise((resolve) => {
                resolve();
            });

            promise.then(() => {
                dropdown[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                });
            });
        },

        toggleDropdown() {
            const dropdown = $(`#${this.id}`);

            if (dropdown.hasClass('show')) {
                this.closeDropdown(dropdown);
            } else {
                this.openDropdown(dropdown);
            }
        },

        openDropdown(dropdown) {
            dropdown.addClass('show');
            document.addEventListener("click", this.closeDropdownOnClickOutside);
            this.setupDropdownTransition(dropdown);
        },

        closeDropdown(dropdown) {
            dropdown.removeClass('show');
            document.removeEventListener("click", this.closeDropdownOnClickOutside);
        },

        closeDropdownOnClickOutside(e) {
            const ref = this.$refs[this.id];

            if (ref && !ref.contains(e.target)) {
                const dropdown = $(`#${this.id}`);

                this.closeDropdown(dropdown);
                document.removeEventListener("click", this.closeDropdownOnClickOutside);
            }
        },

        setupDropdownTransition(dropdown) {
            const transitionEndHandler = () => {
                dropdown[0].removeEventListener('transitionend', transitionEndHandler);
                document.addEventListener('click', (e) => this.closeDropdownOnClickOutside(e, this.id));
                this.scrollIntoViewAfterTransition(dropdown);
            };

            dropdown[0].addEventListener('transitionend', transitionEndHandler);
        },
    }
}
</script>

<style scoped lang="scss">
li {
    position: relative;
    float: left;
    padding: 4px 0;
}

a {
    text-decoration: none;
    color: inherit;
    padding: 10px 15px;
    width: fit-content;
    display: block;
    line-height: 22px;

    &:hover {
        color: rgb(38, 38, 38);
    }
}

.dropdown-menu {
    display: block;
    max-height: 0;
    border: none;
    box-shadow: none;
    overflow: hidden;
    transition: max-height 0.1s;

    li {
        float: none;

        &:hover {
            background: none
        }
    }

    a {
        width: 100%
    }
}

.dropdown.show {
    .caret {
        transform: rotate(180deg);
    }

    .dropdown-menu {
        transition: max-height 0.3s ease-out;
        max-height: 700px;
        border: 1px solid rgba(0, 0, 0, 0.15);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.175);
    }
}

.user-dropdown {
    float: right;
    position: relative;
    text-align: right;
    width: 200px;
    margin: 0;

    li {
        width: 100%;

        a {
            width: 100%;
        }
    }

    .dropdown-menu {
        left: auto;
        right: 0;
    }

    .icon {
        width: 14px;
        height: auto;
        margin-left: -5px;
        margin-right: 5px;
    }

    .dropdown-link {
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }

    .dropdown-link-name {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        
        &:not(:last-child) {
            margin-right: 5px;
        }
    }
}
</style>