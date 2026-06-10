import React, { useState } from "react";

export default function Forms() {
  // const [name, setName] = useState("");
  const [input, setInput] = useState({});

  function handleChange(e) {
    const name = e.target.name;
    const value = e.target.value;

    setInput((values) => ({
      ...values,
      [name]: value,
    }));
  }

  return (
    <form action="">
      <div className="container mt-4">
        <label htmlFor="">Enter Your First Name:</label>
        <br />
        {/* <input
        type="text"
        className="form-control"
        value={name}
        onChange={(e) => setName(e.target.value)}
        placeholder="Enter your name"
      /> */}
        <input
          type="text"
          name="firstName"
          value={input.firstName || ""}
          onChange={handleChange}
          className="form-control"
          placeholder="Enter your first name"
        />
        <br />
        <label htmlFor="">Enter Your Last Name:</label>
        <input
          type="text"
          name="lastName"
          value={input.lastName || ""}
          onChange={handleChange}
          className="form-control"
          placeholder="Enter your last name"
        />
        <br />
        <label htmlFor="">Enter Your Number:</label>
        <input
          type="text"
          name="number"
          value={input.number || ""}
          onChange={handleChange}
          className="form-control"
          placeholder="Enter your phone number"
        />
        <br />
        <label htmlFor="">Enter Your Email:</label>
        <input
          type="email"
          name="email"
          value={input.email || ""}
          onChange={handleChange}
          className="form-control"
          placeholder="Enter your email"
        />
        <br />

        <textarea
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
          name="gender"
          value="Male"
          checked={input.gender === "Male"}
          onChange={handleChange}
        />
        <label htmlFor="male">Male</label>

        <input
          type="radio"
          name="gender"
          value="Female"
          checked={input.gender === "Female"}
          onChange={handleChange}
        />
        <label htmlFor="female">Female</label>
        <br />
        <label htmlFor="">District</label>
        <select
          name="district"
          value={input.district}
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

        {/* <p className="mt-3 text-danger">You typed: {name}</p> */}
        <p className="mt-3 text-danger">
          Name: {input.firstName} {input.lastName}
        </p>

        <p className="mt-3 text-danger">Number: {input.number}</p>

        <p className="mt-3 text-danger">Email: {input.email}</p>
        <p className="mt-3 text-danger">Textarea: {input.textarea}</p>
        <p className="mt-3 text-danger">District: {input.district}</p>
        <p className="mt-3 text-danger">Gender: {input.gender}</p>

        <hr />
      </div>
    </form>
  );
}
