export default [
  {
    title: 'Master',
    icon: { icon: 'tabler-layout-grid-add' },
    children: [
      {
        title: 'Player & Club',
        icon: { icon: 'tabler-shopping-cart-plus' },
        children: [
          {
            title: 'Player',
            to: 'dashboards-player-list',
          },
          {
            title: 'Club',
            to: 'dashboards-club-list',
          },
          {
            title: 'Sport',
            to: 'dashboards-sport-list',
          },
        ],
      },
    ],
  },
]
