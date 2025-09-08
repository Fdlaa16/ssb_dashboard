export default [
{ heading: 'Jadwal' },
  {
    title: 'Jadwal',
    icon: { icon: 'tabler-checkbox' },
    children: [
      { title: 'Jadwal Pertandingan', to: 'dashboards-schedule-match-list', },
      { title: 'Jadwal Latihan', to: 'dashboards-schedule-training-list', },
      { title: 'Klasemen', to: 'dashboards-standing-list', },
    ],
  },
]
