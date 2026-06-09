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
      <label htmlFor="">Enter Your Name</label>
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
        name="fullName"
        value={input.fullName}
        onChange={handleChange}
        className="form-control"
        placeholder="Enter your full name"
      />

      {/* <p className="mt-3 text-danger">You typed: {name}</p> */}
      <p className="mt-3 text-danger">You typed: {input.fullName}</p>
    </div>
  );
}
