<template>
  <div
    class="modal modal-vertical-center fade"
    :id="name"
    data-backdrop="static"
    data-keyboard="false"
  >
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">
            {{ title }}
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </h4>
        </div>
        <template v-if="withSlot">
          <div class="modal-body">
            <slot name="body" />
          </div>
        </template>
        <template v-else>
          <div class="modal-body" v-html="body"></div>
        </template>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
    },
    body: {
      type: String,
    },
    name: {
      type: String,
    },
    withSlot: {
      type: Boolean,
    },
  },
};
</script>

<style scoped></style>
