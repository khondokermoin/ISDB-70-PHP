import { useEffect, useState } from "react";
import axios from "axios";

export default function Displaydata() {
  const [users, setUsers] = useState([]);
  useEffect(() => {
    axios.get("https://jsonplaceholder.typicode.com/users").then((res) => {
      setUsers(res.data);
    });
  }, []);
  return (
    <>
      <div className="container">
        <h1>Display Data Form Server</h1>

        <div>
          {users.map((user) => (
            <p key={user.id}>{user.name}</p>
          ))}
        </div>
        <div>
          <pre>{JSON.stringify(users, null, 2)}</pre>
        </div>
      </div>
    </>
  );
}
