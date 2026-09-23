// src/stores/notification.store.js

import { defineStore } from 'pinia'

export const useNotificationStore =
  defineStore('notification', {
    state: () => ({
      notifications: [],
    }),

    getters: {
      unreadCount: (state) =>
        state.notifications.filter(
          notification => !notification.read
        ).length,
    },

    actions: {
      addNotification(notification) {
        this.notifications.unshift({
          id: Date.now(),
          read: false,
          createdAt: new Date(),
          ...notification,
        })
      },

      markAsRead(id) {
        const notification =
          this.notifications.find(
            item => item.id === id
          )

        if (notification) {
          notification.read = true
        }
      },

      markAllAsRead() {
        this.notifications.forEach(
          notification => {
            notification.read = true
          }
        )
      },

      removeNotification(id) {
        this.notifications =
          this.notifications.filter(
            item => item.id !== id
          )
      },

      clearNotifications() {
        this.notifications = []
      }
    }
  })
