import React from "react";
import PropTypes from "prop-types";
import { Button } from "react-bootstrap";
import Spinner from "./Spinner";
import "./PendingButton.scss";

function PendingButton({ disabled, pending, bsStyle, pendingText, text, onClick }) {
  return (
    <Button className="pending-button" bsStyle={bsStyle} disabled={disabled || pending} onClick={() => onClick()}>
      {pending && <Spinner size="tiny" colour="white" />}
      {pending ? pendingText : text}
    </Button>
  );
}

PendingButton.defaultProps = {
  disabled: false,
  pending: false,
  text: "Save",
  pendingText: "Saving...",
  bsStyle: "primary",
};

PendingButton.propTypes = {
  disabled: PropTypes.bool,
  pending: PropTypes.bool,
  onClick: PropTypes.func.isRequired,
  text: PropTypes.string,
  pendingText: PropTypes.string,
  bsStyle: PropTypes.string,
};

export default PendingButton;
