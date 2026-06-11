import React, { useState } from "react";
import axios from "axios";

export default function Forms() {
  const [input, setInput] = useState({});

  function handleChange(e) {
    const name = e.target.name;
    const value = e.target.value;

    setInput((values) => ({
      ...values,
      [name]: value,
    }));
  }

  // 1. Wrapped axios in a submission lifecycle function
  function handleSubmit(e) {
    e.preventDefault(); // Crucial: Stops page reload which ruins React state

    // Note: Change this to an absolute URL if your PHP is hosted on a different port (e.g. http://localhost/api/user_create.php)
    axios
      .post("http://localhost/react/class%206/app02/api/user_create.php", input)
      .then((response) => {
        console.log("Response from PHP:", response.data);
        alert("Form Data Submitted Successfully!");
        // Optional: clear form by calling setInput({});
      })
      .catch((error) => {
        console.error("Axios Submission Error:", error);
      });
  }

  return (
    // 2. Bound the submission function here and removed empty action attribute
    <form onSubmit={handleSubmit}>
      <div className="container mt-4">
        <label htmlFor="firstName">Enter Your First Name:</label>
        <br />
        <input
          type="text"
          id="firstName"
          name="firstName"
          value={input.firstName || ""}
          onChange={handleChange}
          className="form-control"
          placeholder="Enter your first name"
        />
        <br />
        <label htmlFor="lastName">Enter Your Last Name:</label>
        <input
          type="text"
          id="lastName"
          name="lastName"
          value={input.lastName || ""}
          onChange={handleChange}
          className="form-control"
          placeholder="Enter your last name"
        />
        <br />
        <label htmlFor="number">Enter Your Number:</label>
        <input
          type="text"
          id="number"
          name="number"
          value={input.number || ""}
          onChange={handleChange}
          className="form-control"
          placeholder="Enter your phone number"
        />
        <br />
        <label htmlFor="email">Enter Your Email:</label>
        <input
          type="email"
          id="email"
          name="email"
          value={input.email || ""}
          onChange={handleChange}
          className="form-control"
          placeholder="Enter your email"
        />
        <br />

        <label htmlFor="textarea">Enter Your Address:</label>
        <textarea
          id="textarea"
          name="textarea"
          value={input.textarea || ""}
          onChange={handleChange}
          className="form-control"
          placeholder="Enter your Address"
        />

        <br />
        <label>Gender</label>
        <br />

        <input
          type="radio"
          id="male"
          name="gender"
          value="Male"
          checked={input.gender === "Male"}
          onChange={handleChange}
        />
        <label htmlFor="male" className="ms-1 me-3">
          Male
        </label>

        <input
          type="radio"
          id="female"
          name="gender"
          value="Female"
          checked={input.gender === "Female"}
          onChange={handleChange}
        />
        <label htmlFor="female" className="ms-1">
          Female
        </label>

        <br />
        <br />
        <label htmlFor="district">District</label>
        <select
          id="district"
          name="district"
          value={input.district || ""} // Handled fallback for select tag
          onChange={handleChange}
          className="form-control"
        >
          <option value="">Select District</option>
          <option value="dhaka">Dhaka</option>
          <option value="chattogram">Chattogram</option>
          <option value="rajshahi">Rajshahi</option>
          <option value="khulna">Khulna</option>
          <option value="barishal">Barishal</option>
          <option value="sylhet">Sylhet</option>
          <option value="rangpur">Rangpur</option>
          <option value="mymensingh">Mymensingh</option>
        </select>

        {/* 3. Added an explicit submit button to trigger form submission */}
        <button type="submit" className="btn btn-primary mt-4 w-100">
          Submit Form
        </button>

        <p className="mt-3 text-danger">
          Name: {input.firstName} {input.lastName}
        </p>
        <p className="mt-3 text-danger">Number: {input.number}</p>
        <p className="mt-3 text-danger">Email: {input.email}</p>
        <p className="mt-3 text-danger">Address: {input.textarea}</p>
        <p className="mt-3 text-danger">District: {input.district}</p>
        <p className="mt-3 text-danger">Gender: {input.gender}</p>

        <hr />
      </div>
    </form>
  );
}
