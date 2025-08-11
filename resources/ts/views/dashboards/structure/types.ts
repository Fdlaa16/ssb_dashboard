export interface User {
    id?: number;
    email: string;
    password?: string;
}
  
export interface StructureData {
    id: number; 
    code: string;
    name: string;
    date_of_birth: string; 
    department: string;
    user: User;
    avatar?: File | { url: string; name: string }; 
}
  