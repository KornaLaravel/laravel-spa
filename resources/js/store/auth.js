import axios from 'axios';
import router from '@/router';
import toast from '@/store/toast';

export default {
  namespaced: true,
  state() {
    return {
      authenticated: false,
      user: null,
    };
  },
  getters: {
    user(state) {
      return state.user;
    },
    verified(state) {
      if (state.user) return state.user.email_verified_at;
      return null;
    },
    id(state) {
      if (state.user) return state.user.id;
      return null;
    },
    authenticated(state) {
      return state.authenticated;
    },
  },
  mutations: {
    setAuthentication(state, value) {
      state.authenticated = value;
    },
    setUser(state, payload) {
      state.user = payload;
    },
    setTheme(state, payload) {
      if (payload) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    },
  },
  actions: {
    async login({ dispatch }, payload) {
      try {
        await axios.get('/sanctum/csrf-cookie');
        await axios
          .post('/api/login', payload)
          .then((res) => {
            return dispatch('getUser');
          })
          .catch((err) => {
            throw err.response;
          });
      } catch (e) {
        throw e;
      }
      router.push({ name: 'dashboard' });
    },
    async register({ commit }, payload) {
      await axios.get('/sanctum/csrf-cookie');
      const res = await axios.post('/api/register', payload);
      if (
        res.status == 201 &&
        res &&
        res.data &&
        res.data.user &&
        res.data.user.id
      ) {
        commit('setUser', res.data.user);
        commit('setTheme', res.data.user.theme_dark);
        commit('setAuthentication', true);
        return res;
      }
      commit('setUser', null);
      commit('setAuthentication', false);
      commit('setTheme', null);
      throw res.response;
    },
    async logout({ commit }) {
      await axios
        .post('/api/logout')
        .then((res) => {
          commit('setUser', null);
          commit('setTheme', null);
          commit('setAuthentication', false);
        })
        .catch((err) => {});
      router.push({ name: 'home' });
    },
    async getUser({ commit }) {
      await axios
        .get('/api/user')
        .then((res) => {
          if (res && res.data && res.data.id) {
            commit('setUser', res.data);
            commit('setTheme', res.data.theme_dark);
            commit('setAuthentication', true);
          } else {
            commit('setUser', null);
            commit('setAuthentication', false);
          }
        })
        .catch((err) => {
          commit('setUser', null);
          commit('setTheme', null);
          commit('setAuthentication', false);
          throw err.response;
        });
    },
    async profile({ commit }, payload) {
      const res = await axios.patch('/api/profile', payload);
      if (
        res.status == 200 &&
        res &&
        res.data &&
        res.data.user &&
        res.data.user.id
      ) {
        commit('setUser', res.data.user);
        commit('setTheme', res.data.user.theme_dark);
        commit('setAuthentication', true);
        return res;
      }
      commit('setUser', null);
      commit('setAuthentication', false);
      commit('setTheme', null);
      throw res.response;
    },
    async password({ dispatch }, payload) {
      const res = await axios.patch('/api/password', payload);
      if (
        res.status == 200 &&
        res &&
        res.data &&
        res.data.message &&
        res.data.message == 'Password Updated Successfully'
      ) {
        return 'Success';
      }
      throw res.response;
    },
    async verifyResend({ dispatch }, payload) {
      const res = await axios.post('/api/verify-resend', payload);
      if (res.status != 200) throw res;
      return res;
    },
    async verifyEmail({ dispatch }, payload) {
      const res = await axios.post(
        '/api/verify-email/' + payload.id + '/' + payload.hash,
      );
      if (res.status != 200) throw res;
      dispatch('getUser');
      return res;
    },
    async forgotPassword({ dispatch }, payload) {
      try {
        const response = await axios.post('/api/forgot-password', payload);
        if (
          response &&
          response.status &&
          response.status == 200 &&
          response.data &&
          response.data.message
        ) {
          return response.data.message;
        }
        throw response;
      } catch (error) {
        throw error;
      }
    },
    async resetPassword({ dispatch }, payload) {
      try {
        const response = await axios.post('/api/reset-password', payload);
        if (
          response &&
          response.status &&
          response.status == 200 &&
          response.data &&
          response.data.message
        ) {
          return response.data.message;
        }
        throw response;
      } catch (error) {
        throw error;
      }
    },
  },
};
