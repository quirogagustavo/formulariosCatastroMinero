let contadorSolicitantes = 0;

  function agregarSolicitante() {
  const container = document.getElementById('solicitantes-container');

  const div = document.createElement('div');
  div.classList.add('row', 'g-2', 'mb-2');
  div.dataset.index = contadorSolicitantes;

  div.innerHTML = `
  <div class="col-md-3">
      <input type="text" name="cuit[]" class="form-control cuit-input" placeholder="CUIT del solicitante (opcional)">
    </div>
    <div class="col-md-6">
      <input type="text" name="solicitante[]" class="form-control nombre-input" placeholder="Nombre o Razón Social (opcional)">
    </div>
    <div class="col-md-2">
      <input type="text" name="tipo[]" class="form-control tipo-input" placeholder="Tipo (opcional)" readonly>
    </div>
    <div class="col-md-1 d-flex align-items-center">
      <button type="button" class="btn btn-danger btn-sm" onclick="eliminarSolicitante(this)">X</button>
    </div>
  `;

  container.appendChild(div);
  contadorSolicitantes++;
}

function eliminarSolicitante(btn) {
  btn.closest('.row').remove();
}

document.addEventListener("DOMContentLoaded", function () {
  agregarSolicitante(); // Agrega el primero automáticamente

  document.getElementById('solicitantes-container').addEventListener("blur", function (e) {
  if (e.target && e.target.classList.contains("cuit-input")) {
    const inputCuit = e.target;
    const cuit = inputCuit.value.replace(/\D/g, "");
    
    // Si el CUIT está vacío, permitir continuar sin buscar
    if (cuit.length === 0) return;
    
    // Si el CUIT tiene menos de 11 dígitos y no está vacío, no buscar aún
    if (cuit.length < 11) return;

    // Verificar si ya existe otro input con el mismo CUIT
    const cuitInputs = document.querySelectorAll(".cuit-input");
    let count = 0;
    cuitInputs.forEach(inp => {
      const valorCuit = inp.value.replace(/\D/g, "");
      if (valorCuit.length > 0 && valorCuit === cuit) count++;
    });

    if (count > 1) {
      alert("Este CUIT ya ha sido ingresado. Por favor verifique.");
      inputCuit.value = "";
      inputCuit.focus();
      return;
    }

    const inputNombre = inputCuit.closest(".row").querySelector(".nombre-input");
    const inputTipo = inputCuit.closest(".row").querySelector(".tipo-input");

    fetch(`buscar_cuit.php?cuit=${cuit}`)
      .then(res => {
        if (!res.ok) throw new Error("CUIT no encontrado");
        return res.json();
      })
      .then(data => {
        inputNombre.value = data.nombre || "";
        inputTipo.value = data.tipo || "";
      })
      .catch(err => {
        console.warn(err);
        inputNombre.value = "";
      });
  }
}, true);
});