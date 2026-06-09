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

      {/* <p className="mt-3 text-danger">You typed: {name}</p> */}
      <p className="mt-3 text-danger">
        Name: {input.firstName} {input.lastName}
      </p>

      <p className="mt-3 text-danger">Number: {input.number}</p>

      <p className="mt-3 text-danger">Email: {input.email}</p>
    </div>
  );
}
