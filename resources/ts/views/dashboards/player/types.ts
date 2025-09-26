export interface User {
  id?: number;
  email: string;
  password?: string;
}

export interface Club {
  id: number;
  code: string;
  name: string;
  profile_club?: {
    id: number;
    url: string;
    name: string;
  };
}

// export interface ClubPlayer {
//   club_id: number | string; 
//   player_id: number;
//   back_number: string;   
//   position: string;
//   category: string; 
//   is_captain: boolean;   
//   status: boolean;
// }

export interface Sport {
  id: number;
  code: string;
  name: string;
}

export interface SportPlayer {
  id: number;
  player_id: string;
  sport_id: Sport['id'];
}

export interface PlayerData {
  id: number; 
  code: string;
  name: string;
  nisn: string;
  phone: string;
  height: string;
  weight: string;
  back_number: string;   
  position: string;
  category: string; 
  is_captain: boolean;   
  status: number;
  user: User; 
  club: Club;
  sports?: Sport[];
  sport_players?: SportPlayer[];
  family_card?: File | { url: string; name: string };
  report_grades?: File | { url: string; name: string };
  birth_certificate?: File | { url: string; name: string };
  proof_payment?: File | { url: string; name: string };
  avatar?: File | { url: string; name: string }; 
}
