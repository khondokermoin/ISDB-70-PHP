import React, { useEffect, useState } from "react";
import axios from "axios";

export default function Userlist() {
  const [users, setUsers] = useState([]);

  useEffect(() => {
    axios
      .get("http://localhost/react/class%207/app02/api/user_list.php")
      .then((res) => {
        setUsers(res.data);
      })
      .catch((err) => {
        console.log(err);
      });
  }, []);

  return (
    <div className="container mt-5">
      <div className="table-responsive">
        <table className="table table-striped table-bordered table-hover text-center align-middle">
          <thead className="table-dark">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Phone</th>
              <th>Email</th>
              <th>Address</th>
              <th>Gender</th>
              <th>District</th>
            </tr>
          </thead>

          <tbody>
            {users.length > 0 ? (
              users.map((user) => (
                <tr key={user.id}>
                  <td>{user.id}</td>
                  <td>{user.first_name + " " + user.last_name}</td>
                  <td>{user.phone_number}</td>
                  <td>{user.email}</td>
                  <td>{user.address}</td>
                  <td>{user.gender}</td>
                  <td>{user.district}</td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan="7" className="text-center py-4">
                  <div className="spinner-border text-primary" role="status">
                    <span className="visually-hidden">Loading...</span>
                  </div>
                  <p className="mt-2">Loading users...</p>
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}
