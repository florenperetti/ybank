export const state = () => ({
  id: null
});

export const mutations = {
  login (state, id) {
    state.id = id;
  },
  logout (state) {
    state.id = null;
  }
};
