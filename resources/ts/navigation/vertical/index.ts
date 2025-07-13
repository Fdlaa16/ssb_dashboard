import type { VerticalNavItems } from '@layouts/types'
import appsAndPages from './apps-and-pages'
import charts from './charts'
import dashboard from './dashboard'
import forms from './forms'
import master from './master'
import others from './others'
import schedule from './schedule'
import uiElements from './ui-elements'

export default [
    ...master, 
    ...schedule, 
    ...appsAndPages, 
    ...charts, 
    ...dashboard, 
    ...forms, 
    ...others, 
    ...uiElements
] as VerticalNavItems
