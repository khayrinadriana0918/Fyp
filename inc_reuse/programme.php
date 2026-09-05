
<style>
/* Button used to open the contact form - fixed at the bottom of the page */
.open-button {
  background-color: #555;
  color: white;
  padding: 16px 20px;
  border: none;
  cursor: pointer;
  opacity: 0.8;
  position:absolute;
  bottom: 23px;
  right: 28px;
  width: 280px;
}

/* The popup form - hidden by default */
.form-popup {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 9999;

  justify-content: center;
  align-items:center;

}

/* Add styles to the form container */
.form-container {
  max-width: 300px;
  padding: 10px;
  background-color: white;
}

/* Full-width input fields */
.form-container select{
  width: 100%;
  padding: 15px;
  margin: 5px 0 22px 0;
  border: none;
  background: #f1f1f1;
}

/* When the inputs get focus, do something */
.form-container select:focus{
  background-color: #ddd;
  outline: none;
}

/* Set a style for the submit/login button */
.form-container .btn {
  background-color: #04AA6D;
  color: white;
  padding: 16px 20px;
  border: none;
  cursor: pointer;
  width: 100%;
  margin-bottom:10px;
  opacity: 0.8;
}

/* Add a red background color to the cancel button */
.form-container .cancel {
  background-color: red;
}

/* Add some hover effects to buttons */
.form-container .btn:hover, .open-button:hover {
  opacity: 1;
}
</style>
<script>
    function openForm() {
        document.getElementById("myForm").style.display = "flex";
    }

    function closeForm() {
        document.getElementById("myForm").style.display = "none";
    }
</script>

    <!-- A button to open the popup form -->
<button class="open-button" onclick="openForm()">Choose Your Course Here</button>

<!-- The form -->
<div class="form-popup" id="myForm">
  <form action="/action_page.php" class="form-container">
    <fieldset>
        <legend>Choose Code Programme</legend>
        <label for="programme">What is your code programme?</label>
        <select id="programme" name="programme">
            <option value="programme1">Programme 1</option>
        </select>
    </fieldset>
    <button type="submit" class="btn">Submit</button>
    <button type="button" class="btn cancel" onclick="closeForm()">Close</button>
  </form>
</div>

