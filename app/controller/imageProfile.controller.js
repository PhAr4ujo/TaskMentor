"use strict"
const PATH_API = "../../../TaskMentor/app/model/imageProfile.php";

export async function pegarImagens() {
  const formData = new FormData();
  formData.append('operation', 'read');

  const request = await fetch(PATH_API, {
    method: 'POST',
    body: formData
  });
  
  const response = await request.json();
  return response;
};