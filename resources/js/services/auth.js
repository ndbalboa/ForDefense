import axios from 'axios';

export async function isAuthenticated() {
  try {
    const response = await axios.get('/api/user');
    return response.status === 200;
  } catch (error) {
    return false;
  }
}

export async function getUserRole() {
  try {
    const response = await axios.get('/api/user');
    return response.data.role; 
  } catch (error) {
    return null;
  }
}

export async function logout() {
  await axios.post('/api/logout');
  localStorage.removeItem('user'); 
}

