export default [
  {
    title: 'Jadwal',
    icon: { icon: 'tabler-layout-grid-add' },
    children: [
      {
        title: 'Jadwal',
        icon: { icon: 'tabler-shopping-cart-plus' },
        children: [
          {
            title: 'Jadwal Pertandingan',
            to: 'dashboards-schedule-match-list',
          },
          {
            title: 'Jadwal Latihan',
            to: 'dashboards-schedule-training-list',
          },
          {
            title: 'Klasemen',
            to: 'dashboards-standing-list',
          },
        ],
      },
    ],
  },
]
