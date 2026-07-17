import { FileText, Upload, Trash2 } from "lucide-react";
import { Button } from "./button";

interface FileUploadBoxProps {
  label: string;
  fileName: string | null;
  onUpload: () => void;
  onRemove: () => void;
  disabled?: boolean;
  /** Documenta obligatoriedad en la API del componente; no emite aria-required (ver describedById). */
  required?: boolean;
  /**
   * Id de la región de estado/error asociada a este campo (aria-describedby).
   * FileUploadBox es un <button> que abre un diálogo de archivos, no un control de
   * formulario (textbox/combobox) — aria-required no es un atributo válido sobre
   * role=button (falla aria-allowed-attr en axe). La obligatoriedad y los errores se
   * comunican vía el nombre accesible del campo (label con sr-only) y esta descripción.
   */
  describedById?: string;
}

export function FileUploadBox({
  label,
  fileName,
  onUpload,
  onRemove,
  disabled = false,
  describedById,
}: FileUploadBoxProps) {
  return (
    <div className="flex items-center gap-3 p-4 bg-white rounded-[14px] border-2 border-[#4B236A]/20">
      <FileText className="w-6 h-6 text-[#4B236A] flex-shrink-0" aria-hidden="true" />
      <div className="flex-1 min-w-0">
        <p className="text-sm text-gray-700">{label}</p>
        {fileName && (
          <p className="text-xs text-green-600 mt-1 truncate">
            ✓ {fileName}
          </p>
        )}
      </div>
      {fileName ? (
        <Button
          type="button"
          variant="outline"
          size="sm"
          onClick={onRemove}
          disabled={disabled}
          aria-describedby={describedById}
          aria-label={`Eliminar ${fileName}`}
          className="text-red-600 hover:text-red-700 hover:bg-red-50 rounded-[14px] flex-shrink-0"
        >
          <Trash2 className="w-4 h-4" aria-hidden="true" />
        </Button>
      ) : (
        <Button
          type="button"
          size="sm"
          onClick={onUpload}
          disabled={disabled}
          aria-describedby={describedById}
          aria-label={label}
          className="bg-[#4B236A] hover:bg-[#4B236A]/90 text-white rounded-[14px] flex-shrink-0"
        >
          <Upload className="w-4 h-4 mr-2" aria-hidden="true" />
          Cargar
        </Button>
      )}
    </div>
  );
}
