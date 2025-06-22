export interface User {
  id?: number;
  email: string;
  password?: string;
}

export interface Club {
  id: number;
  code: string;
  name: string;
}

export interface ClubPlayer {
  club_id: number;
  player_id: number;
  back_number: string;   
  position: string;
  is_captain: boolean;   
  status: boolean;
  category: string; 
}

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
  name: string;
  nisn: string;
  height: string;
  weight: string;
  user: User; 
  club: Club;
  club_player: ClubPlayer;
  sports?: Sport[];
  sport_players?: SportPlayer[];
  family_card?: File;
  report_grades?: File;
  birth_certificate?: File;
  avatar?: File; 
}

