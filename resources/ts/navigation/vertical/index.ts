import type { VerticalNavItems } from '@layouts/types'
import master from './master'
import schedule from './schedule'
// import appsAndPages from './apps-and-pages'
// import charts from './charts'
// import dashboard from './dashboard'
// import forms from './forms'
// import others from './others'
// import uiElements from './ui-elements'

export default [
    ...master, 
    ...schedule, 
    // ...appsAndPages, 
    // ...charts, 
    // ...dashboard, 
    // ...forms, 
    // ...others, 
    // ...uiElements
] as VerticalNavItems
