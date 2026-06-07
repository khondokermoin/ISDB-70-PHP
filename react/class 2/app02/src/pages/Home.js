import React from "react";
import Header from "../components/Header";
import Footer from "../components/Footer";
import Contact from "./Contact";
import About from "./About";

export default function Home() {
  return (
    <>
      <main>
        <section id="home" className="bg-primary text-white text-center p-5">
          <div className="container">
            <h1>Welcome to My Website</h1>
            <p className="lead">Simple one-page Bootstrap layout</p>
          </div>
        </section>

        <About />

        <section id="services" className="bg-light p-5">
          <div className="container">
            <h2>Services</h2>

            <div className="row">
              <div className="col-md-4">
                <div className="card p-3 mb-3">
                  <h5>Web Design</h5>
                  <p>Modern and responsive designs.</p>
                </div>
              </div>

              <div className="col-md-4">
                <div className="card p-3 mb-3">
                  <h5>Development</h5>
                  <p>Clean and efficient code.</p>
                </div>
              </div>

              <div className="col-md-4">
                <div className="card p-3 mb-3">
                  <h5>SEO</h5>
                  <p>Improve your search ranking.</p>
                </div>
              </div>
            </div>
          </div>
        </section>
      </main>
      <Contact />
    </>
  );
}
