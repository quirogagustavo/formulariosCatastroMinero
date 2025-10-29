async function buscarExpediente() {
  const reparticion = document.querySelector('[name="reparticion"]').value.trim().toUpperCase();
  const numExp = document.querySelector('[name="num_exp"]').value.trim();
  const ano = document.querySelector('[name="ano"]').value.trim();

  if (!reparticion || !numExp || !ano) return;

  const params = new URLSearchParams({
    reparticion,
    num_exp: numExp,
    ano
  });

  try {
    const response = await fetch('buscar_expediente_get2.php?' + params.toString());

    if (!response.ok) throw new Error("HTTP error");

    const data = await response.json();

    if (!data.expediente) throw new Error("Sin expediente");   
    completarFormulario(data.expediente);
    document.querySelector('[name="denominacion"]').value = data.expediente.denom;
    console.log("Denominación:", data.expediente.denom);
  } catch (error) {
    alert("No se encontró el expediente");
    const campo = document.querySelector('[name="iniciador"]');
    campo.value = '';
    document.querySelector('[name="reparticion"]').value = '';
    document.querySelector('[name="num_exp"]').value = '';
    const ano = document.querySelector('[name="ano"]').value = '';
  
  }
}


