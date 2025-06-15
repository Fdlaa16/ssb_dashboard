export default [
  {
    title: 'Schedule',
    icon: { icon: 'tabler-layout-grid-add' },
    children: [
      {
        title: 'Schedule Player',
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
        ],
      },
    ],
  },
]
