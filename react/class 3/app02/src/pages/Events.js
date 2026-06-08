import React from "react";

export default function Events() {
  const containerStyle = {
    height: "100vh",
    width: "100%",
    display: "flex",
    justifyContent: "center",
    alignItems: "start",
  };

  const shoot = () => {
    alert("Great Shot!");
  };
  return (
    <>
      <div className="container" style={containerStyle}>
        <button onClick={shoot}>Take the Shot!</button>
      </div>
    </>
  );
}
