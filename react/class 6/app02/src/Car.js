import React from 'react'

export default function Car({ brand, ...rest }) {
  return (
    <>
      <h2>Car Details</h2>

      <p>Brand: {brand}</p>

      <h4>Other Details:</h4>

      <ul>
        {Object.entries(rest).map(([key, value]) => (
          <li key={key}>
            {key}: {value}
          </li>
        ))}
      </ul>
    </>
  );
}
