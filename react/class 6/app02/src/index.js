import React from "react";
import ReactDOM from "react-dom/client";
import "./index.css";
import App from "./App";
import Car from "./Car";
import reportWebVitals from "./reportWebVitals";
import { BrowserRouter } from "react-router-dom";
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap/dist/js/bootstrap.bundle.min.js";

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);

/* const abcRoot = ReactDOM.createRoot(document.getElementById("abc"));
abcRoot.render(
  <React.StrictMode>
    <Car brand="Toyota" color="Red" model="Corolla" year={2024} />
  </React.StrictMode>,
);
 */
reportWebVitals();
