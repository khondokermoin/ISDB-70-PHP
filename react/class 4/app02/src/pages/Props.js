import React from "react";


export default function Props() {
  const paraStyle = {
    color: "white",
    backgroundColor: "#000000",
    padding: "15px",
    fontSize: "16px",
  };

  const containerStyle = {
    height: "100vh",
    width: "100%",
    display: "flex",
    justifyContent: "center",
    alignItems: "start",
  };

  const person = {
    name: "Moin",
    age: 20,
    address: "Dhanmondi",
  };

  // Destructuring
  const { name, age} = person;

  // ..Rest
  const { name: personName, ...rest } = person;

  return (
    <div className="container" style={containerStyle}>
      <p style={paraStyle}>
        <h3>Object property output</h3>
        Name: {person.name}
        <br />
        Age: {person.age}
        <br />
        Address: {person.address}
        <br />
        <br />
        <h3>Destructuring output</h3>
        Name: {person.name}
        <br />
        Age: {person.age}
        <br />
        <br />
        <h3>... Rest output</h3>
        <ul>
        {Object.entries(rest).map(([key, value]) => (
          <li key={key}>
            {key}: {value}
          </li>
        ))}
      </ul>


      </p>
    </div>
  );
}
