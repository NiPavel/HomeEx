import { useState } from "react";
import "./App.css";
import ButtonsWrapper from "./components/ButtonsWrapper";
import Notification from "./components/Notification";
import Form from "./components/Form";
import ReportShow from "./components/ReportShow";

function App() {
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");

  const [sensors, setSensors] = useState([]);
  const [aggregatedReport, setAggregatedReport] = useState([]);
  const [malfunctioningReport, setMalfunctioningReport] = useState([]);

  const [addSensor, setAddSensor] = useState(false);
  const [deleteSensor, setDeleteSensor] = useState(false);
  const [showSensors, setShowSensors] = useState(false);
  const [aggregatedHourly, setAggregatedHourly] = useState(false);
  const [malfunctioningSensors, setMalfunctioningSensors] = useState(false);

  const addSensorHandler = () => {
    setAddSensor(true);
  };

  const deleteSensorHandler = () => {
    setDeleteSensor(true);
  };

  const showSensorsHandler = async () => {
    setShowSensors(true);
    try {
      const response = await fetch(`http://localhost:3030/sensors`, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      });

      if (!response.ok) {
        throw new Error(
          `HTTP error! Status: ${response.status} ${response.statusText}`
        );
      }

      const res = await response.json();
      setSensors(res);
    } catch (err) {
      setError(err.message);
      console.error("Error fetching sensors:", err.message);
    }
  };

  const aggregatedHourlyHandler = async () => {
    setAggregatedHourly(true);
    try {
      const response = await fetch(
        `http://localhost:3030/reports/hourly-averages`,
        {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
        }
      );

      if (!response.ok) {
        throw new Error(
          `HTTP error! Status: ${response.status} ${response.statusText}`
        );
      }

      const res = await response.json();
      setAggregatedReport(res);
    } catch (err) {
      setError(err.message);
      console.error("Error fetching aggreated hourly report:", err.message);
    }
  };

  const malfunctioningSensorsHandler = async () => {
    setMalfunctioningSensors(true);
    try {
      const response = await fetch(
        `http://localhost:3030/reports/malfunctioning-sensors`,
        {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
        }
      );

      if (!response.ok) {
        throw new Error(
          `HTTP error! Status: ${response.status} ${response.statusText}`
        );
      }

      const res = await response.json();
      setMalfunctioningReport(res);
    } catch (err) {
      setError(err.message);
      console.error(
        "Error fetching malfunctioning sensors report:",
        err.message
      );
    }
  };

  const add100Sensors = async () => {
    try {
      const response = await fetch("http://localhost:3030/add-100-sensors", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
      });

      if (!response.ok) {
        throw new Error(
          `HTTP error! Status: ${response.status} ${response.statusText}`
        );
      }

      const res = await response.json();
      setMessage(res);
    } catch (err) {
      setError(err.message);
      console.error("Error fetching addin 100 sensors:", err.message);
    }
  };

  const sensorControl = [
    { title: "Add Sensor", onClick: addSensorHandler },
    { title: "Add 100 Sensors", onClick: add100Sensors },
    { title: "Show all sensors", onClick: showSensorsHandler },
    { title: "Delete Sensor", onClick: deleteSensorHandler },
  ];

  const reports = [
    {
      title: "Aggregated hourly temperatures for the past week",
      onClick: aggregatedHourlyHandler,
    },
    { title: "Malfunctioning sensors", onClick: malfunctioningSensorsHandler },
  ];

  return (
    <div className="flex flex-col justify-around items-center bg-cyan-50 h-screen w-full">
      {(message || error) && (
        <Notification message={message.message} error={error} open={true} />
      )}

      <ButtonsWrapper title={"Sensor Control"} buttons={sensorControl} />
      {addSensor && (
        <Form
          title={"Add Sensor"}
          label={"Enter a face for sensor: "}
          action={"Add"}
          setMessage={setMessage}
          setError={setError}
        />
      )}
      {deleteSensor && (
        <Form
          title={"Delete Sensor"}
          label={"Enter an id of sensor: "}
          action={"Delete"}
          setMessage={setMessage}
          setError={setError}
        />
      )}
      {showSensors && sensors && (
        <ReportShow
          showSensors={showSensors}
          setShowSensors={setShowSensors}
          sensors={sensors}
          title={"All sensors"}
        />
      )}
      {aggregatedHourly && aggregatedReport && (
        <ReportShow
          showSensors={aggregatedHourly}
          setShowSensors={setAggregatedHourly}
          aggregatedReport={aggregatedReport}
          title={"Aggreagated Hourly Report For Last Week"}
        />
      )}
      {malfunctioningSensors && malfunctioningReport && (
        <ReportShow
          showSensors={malfunctioningSensors}
          setShowSensors={setMalfunctioningSensors}
          malfunctioningReport={malfunctioningReport}
          title={"Malfunctioning sensors"}
        />
      )}

      <ButtonsWrapper title={"Reports"} buttons={reports} />
    </div>
  );
}

export default App;
