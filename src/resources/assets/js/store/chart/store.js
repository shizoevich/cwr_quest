export default {
  state: {
    isLoading: false,
  },

  mutations: {
    setChartLoadingStatus(state, status) {
      state.isLoading = status;
    }
  }
};