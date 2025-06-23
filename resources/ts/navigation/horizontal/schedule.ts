export default [
  {
    title: 'Schedule',
    icon: { icon: 'tabler-layout-grid-add' },
    children: [
      {
        title: 'Schedule',
        icon: { icon: 'tabler-shopping-cart-plus' },
        children: [
          {
            title: 'Schedule Match',
            to: 'dashboards-schedule-match-list',
          },
          {
            title: 'Schedule Training',
            to: 'dashboards-schedule-training-list',
          },
          {
            title: 'Standings',
            to: 'dashboards-standing-list',
          },
        ],
      },
    ],
  },
]
