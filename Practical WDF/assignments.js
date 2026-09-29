document
  .getElementById("close-btn")
  .addEventListener("click", function (event) {
    let btn = document.getElementById("close-btn");
    event.preventDefault();
    window.location.href = "Pract1.html";
  });
let allAssignments = {};
let currentAssignments = [];
let currentAssignment = 0;
let subjectSelect = document.getElementById("subjectSelect");
let assignmentContainer = document.getElementById("assignmentContainer");
let nextAssignment = document.getElementById("nextAssignment");
let prevAssignment = document.getElementById("prevAssignment");
fetch("assignments.json") .then(function(response) {
      if (!response.ok) {
      throw new Error("Could not find assignments.json");
      }
      return response.json();
    }).then(function(data) {
    allAssignments = data.assignments;
    console.log("Assignments loaded:", allAssignments);
    })
    .catch(function(error) {
    console.log("Error:", error);
    });
subjectSelect.addEventListener("change", function() {
    let selectedSubject = subjectSelect.value;
    if (selectedSubject === "") {
        currentAssignments = [];
        currentAssignment = 0;
        assignmentContainer.innerHTML = `
            <p>
                Please select a subject to view assignments.
            </p>
        `;
        return;
    }
    currentAssignments =
        allAssignments[selectedSubject];
    currentAssignment = 0;
    showAssignment();
});
function showAssignment() {
    if( !currentAssignments || currentAssignments.length === 0 ){
        assignmentContainer.innerHTML = `<p>No assignments available for this subject.</p>`;
        return;
    }
    let assignment = currentAssignments[currentAssignment];
    assignmentContainer.innerHTML = `
        <article class="assignment-item">
            <h3>${assignment.title}</h3>
            <p><strong>Description:</strong>
                ${assignment.description}
            </p>
            <p><strong>Deadline:</strong>
                ${assignment.deadline}
            </p>
        </article>
    `;
}
nextAssignment.addEventListener("click",function(){
  if(currentAssignment.length === 0){
    return;
  }
  currentAssignment++;
  if(currentAssignment>=currentAssignment.length){
    currentAssignment = 0;
  }
  showAssignment();
});
prevAssignment.addEventListener("click",function(){
 if(currentAssignment.length === 0){
  return;
 }
 currentAssignment--;
 if(currentAssignment<0){
  currentAssignment = currentAssignment.length - 1;
 }
 showAssignment();
});
