import React from "react";
import { Link } from "react-router-dom";
import "bootstrap/dist/css/bootstrap.min.css";

export default function Header() {
  return (
    <nav className="navbar navbar-expand-lg navbar-dark bg-dark">
      <div className="container">
        {/* BRAND */}
        <Link className="navbar-brand" to="/">
          MySite
        </Link>

        {/* TOGGLER */}
        <button
          className="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#nav"
        >
          <span className="navbar-toggler-icon"></span>
        </button>

        {/* MENU */}
        <div className="collapse navbar-collapse" id="nav">
          <ul className="navbar-nav ms-auto">
            {/* HOME */}
            <li className="nav-item">
              <Link className="nav-link" to="/">
                Home
              </Link>
            </li>

            {/* MEGA MENU */}
            <li className="nav-item dropdown position-static">
              <a
                className="nav-link dropdown-toggle"
                href="#"
                data-bs-toggle="dropdown"
              >
                Services
              </a>

              <div className="dropdown-menu mega-menu bg-light p-3 w-100">
                <div className="container">
                  <div className="row">
                    <div className="col-md-3">
                      <h6>Web Design</h6>
                      <a className="dropdown-item" href="#">
                        Landing Pages
                      </a>
                      <a className="dropdown-item" href="#">
                        UI Design
                      </a>
                      <a className="dropdown-item" href="#">
                        Responsive Design
                      </a>
                    </div>

                    <div className="col-md-3">
                      <h6>Development</h6>
                      <a className="dropdown-item" href="#">
                        Frontend
                      </a>
                      <a className="dropdown-item" href="#">
                        Backend
                      </a>
                      <a className="dropdown-item" href="#">
                        Full Stack
                      </a>
                    </div>

                    <div className="col-md-3">
                      <h6>Marketing</h6>
                      <a className="dropdown-item" href="#">
                        SEO
                      </a>
                      <a className="dropdown-item" href="#">
                        Social Media
                      </a>
                      <a className="dropdown-item" href="#">
                        Email Campaigns
                      </a>
                    </div>

                    <div className="col-md-3">
                      <h6>Support</h6>
                      <a className="dropdown-item" href="#">
                        Help Center
                      </a>
                      <a className="dropdown-item" href="#">
                        Live Chat
                      </a>
                      <a className="dropdown-item" href="#">
                        Contact Us
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </li>

            {/* ABOUT */}
            <li className="nav-item">
              <Link className="nav-link" to="/about">
                About
              </Link>
            </li>

            {/* CONTACT */}
            <li className="nav-item">
              <Link className="nav-link" to="/contact">
                Contact
              </Link>
            </li>

            {/* PROPS */}
            <li className="nav-item">
              <Link className="nav-link" to="/props">
                Props
              </Link>
            </li>

            {/* EVENTS */}
            <li className="nav-item">
              <Link className="nav-link" to="/events">
                Events
              </Link>
            </li>

            {/* LIST */}
            <li className="nav-item">
              <Link className="nav-link" to="/list">
                List
              </Link>
            </li>

            {/* FORMS */}
            <li className="nav-item">
              <Link className="nav-link" to="/forms">
                Form
              </Link>
            </li>

            {/* Display Data */}
            <li className="nav-item">
              <Link className="nav-link" to="/displaydata">
                Display Data
              </Link>
            </li>
            {/* User list */}
            <li className="nav-item">
              <Link className="nav-link" to="/userlist">
                User List
              </Link>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  );
}
