import { setupLayouts } from 'virtual:generated-layouts'
import type { App } from 'vue'

import type { RouteRecordRaw } from 'vue-router/auto'

import { createRouter, createWebHistory } from 'vue-router/auto'

import { redirects, routes } from './additional-routes'
import { setupGuards } from './guards'

function recursiveLayouts(route: RouteRecordRaw): RouteRecordRaw {
  if (route.children) {
    for (let i = 0; i < route.children.length; i++)
      route.children[i] = recursiveLayouts(route.children[i])

    return route
  }

  return setupLayouts([route])[0]
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior(to) {
    if (to.hash)
      return { el: to.hash, behavior: 'smooth', top: 60 }

    return { top: 0 }
  },
  extendRoutes: pages => [
    ...redirects,
    ...[
      ...pages,
      ...routes,
    ].map(route => recursiveLayouts(route)),
  ],
})

// router.beforeEach((to, from, next) => {
  // const user = ref(null)

  // console.log('asdasd', to.fullPath)
  // if (to.fullPath == '/dashboards/schedule_match/list') {
  //   console.log('lu ga boleh')
  //   router.push('/dashboards/crm')
  // }
  // next()
// })

setupGuards(router)

export { router }

export default function (app: App) {
  app.use(router)
}
