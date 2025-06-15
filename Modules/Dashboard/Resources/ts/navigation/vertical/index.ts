import type { VerticalNavItems } from '../../@layouts/types'
// import appsAndPages from './apps-and-pages'
// import charts from './charts'
// import dashboard from './dashboard'
// import forms from './forms'
// import others from './others'
// import uiElements from './ui-elements'
import master from './master'
import schedule from './schedule'

export default [...dashboard, ...appsAndPages, ...uiElements, ...forms, ...charts, ...others, ...master, ...schedule] as VerticalNavItems
