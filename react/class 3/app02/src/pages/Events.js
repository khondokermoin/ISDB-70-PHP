import React from "react";

export default function Events() {
  const containerStyle = {
    height: "100vh",
    width: "100%",
    // display: "flex",
    justifyContent: "center",
    alignItems: "start",
  };

  const shoot = () => {
    alert("Great Shot!");
  };

  const Ano = (x) => {
    alert(x);
  };

  const Today = "Tuesday";
  return (
    <>
      {/* <div className="container" style={containerStyle}>
        <button onClick={shoot}>Take the Shot!</button>
      </div> */}

      <div className="container" style={containerStyle}>
        <button onClick={shoot}>Cleck me</button>
        <br />
        <button onClick={() => Ano("Good Shot Moin")}>Take the Shot!</button>

        <h3>Condition</h3>
        {Today == "Tuesday" ? "Office is Open: " : "Office is Close"}
        <h3>Logical && Operator</h3>
        {Today == "Tuesday" && "Office is Open: "}
        {Today == "Friday" && "Office is Open: "} {/* no output (condition not match)*/}
      </div>
    </>
  );
}
